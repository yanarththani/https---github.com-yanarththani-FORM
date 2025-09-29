<?php
namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormField;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FormController extends Controller
{
    protected $googleSheetsService;

    public function __construct(GoogleSheetsService $googleSheetsService)
    {
        $this->googleSheetsService = $googleSheetsService;
    }

    /**
     * Get all forms for authenticated user
     */
    public function index(Request $request)
    {
        // try {
        //     $request = Form::with(['creator', 'responses'])
        //         // ->byUser(Auth::id())
        //         ->orderBy('created_at', 'desc')
        //         ->get()
        //         ->map(function ($request) {
        //             return [
        //                 'id' => $request->id,
        //                 'form_title' => $request->title,
        //                 'form_description' => $request->description,
        //                 'status' => $request->is_active,
        //                 // 'response_count' => $request->response_count,
        //                 // 'created_by' => $request->creator->name,
        //                 // 'created_at' => $request->created_at->format('M d, Y H:i'),
        //                 // 'updated_at' => $request->updated_at->format('M d, Y H:i'),
        //             ];
        //         });

        //     return response()->json([
        //         'success' => true,
        //         'data' => $request
        //     ]);

        // } catch (\Exception $e) {
        //     Log::error('Form index error: ' . $e->getMessage());

        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Failed to load forms'
        //     ], 500);
        // }
        return view('login');
        compact('request');
    }





//     public function index()
// {
//     try {
//         $formData = DB::table('forms')->first();
        
//         if (!$formData) {
//             // Return default form structure
//             return response()->json([
//                 'success' => true,
//                 'data' => [
//                     'form_title' => 'Sample Form',
//                     'form_description' => 'This is a sample form',
//                     'form_fields' => []
//                 ]
//             ]);
//         }

//         return response()->json([
//             'success' => true,
//             'data' => [
//                 'form_title' => $formData->form_title,
//                 'form_description' => $formData->form_description,
//                 'form_fields' => json_decode($formData->form_fields, true) ?: []
//             ]
//         ]);

//     } catch (\Exception $e) {
//         // Return default data on error
//         return response()->json([
//             'success' => true,
//             'data' => [
//                 'form_title' => 'Default Form',
//                 'form_description' => 'Form description',
//                 'form_fields' => []
//             ]
//         ]);
//     }
// }



    /**
     * Save or update form
     */
    public function save(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'formId' => 'nullable|exists:forms,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
                'fields' => 'required|array|min:1',
                'fields.*.type' => 'required|string|in:text,email,textarea,number,date,multiple-choice,checkboxes,dropdown',
                'fields.*.question' => 'required|string|max:255',
                'fields.*.placeholder' => 'nullable|string|max:500',
                'fields.*.required' => 'boolean',
                'fields.*.options' => 'nullable|array',
                'fields.*.order' => 'integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $formData = $validator->validated();

            // Create or update form
            if (!empty($formData['formId'])) {
                $form = Form::findOrFail($formData['formId']);
                
                // Check ownership
                if ($form->created_by !== Auth::id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized to edit this form'
                    ], 403);
                }

                $form->update([
                    'title' => $formData['title'],
                    'description' => $formData['description'],
                    'is_active' => $formData['status'] === 'active',
                    'updated_by' => Auth::id(),
                ]);

                // Delete existing fields
                $form->fields()->delete();
            } else {
                $form = Form::create([
                    'title' => $formData['title'],
                    'description' => $formData['description'],
                    'is_active' => $formData['status'] === 'active',
                    'created_by' => Auth::id(),
                    'google_sheet_id' => $this->generateGoogleSheetId(),
                ]);
            }

            // Create form fields
            foreach ($formData['fields'] as $index => $fieldData) {
                FormField::create([
                    'form_id' => $form->id,
                    'type' => $fieldData['type'],
                    'question' => $fieldData['question'],
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'is_required' => $fieldData['required'] ?? false,
                    'options' => !empty($fieldData['options']) ? $fieldData['options'] : null,
                    'order' => $fieldData['order'] ?? $index,
                ]);
            }

            // Initialize Google Sheet for new forms
            if (empty($formData['formId'])) {
                $this->initializeGoogleSheet($form);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Form saved successfully',
                'formId' => $form->id,
                'google_sheet_id' => $form->google_sheet_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Form save error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error saving form: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single form with fields
     */
    public function show($id)
    {
        try {
            $form = Form::with('fields')->findOrFail($id);

            // Check ownership
            if ($form->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to access this form'
                ], 403);
            }

            $formData = [
                'id' => $form->id,
                'title' => $form->title,
                'description' => $form->description,
                'is_active' => $form->is_active,
                'google_sheet_id' => $form->google_sheet_id,
                'fields' => $form->fields->map(function ($field) {
                    return [
                        'type' => $field->type,
                        'question' => $field->question,
                        'placeholder' => $field->placeholder,
                        'is_required' => $field->is_required,
                        'options' => $field->options ?? [],
                        'order' => $field->order,
                    ];
                })->sortBy('order')->values()
            ];

            return response()->json([
                'success' => true,
                'data' => $formData
            ]);

        } catch (\Exception $e) {
            Log::error('Form show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Form not found'
            ], 404);
        }
    }

    /**
     * Toggle form status
     */
    public function toggleStatus($id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'is_active' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $form = Form::findOrFail($id);

            // Check ownership
            if ($form->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this form'
                ], 403);
            }

            $form->update(['is_active' => $request->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Form status updated successfully',
                'is_active' => $form->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Form toggle status error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error updating form status'
            ], 500);
        }
    }

    /**
     * Delete form
     */
    public function destroy($id)
    {
        try {
            $form = Form::findOrFail($id);

            // Check ownership
            if ($form->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this form'
                ], 403);
            }

            $form->delete();

            return response()->json([
                'success' => true,
                'message' => 'Form deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Form delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error deleting form'
            ], 500);
        }
    }

    /**
     * Sync form data with Google Sheet
     */
    public function syncGoogleSheet($id)
    {
        try {
            $form = Form::with(['fields', 'responses', 'creator'])->findOrFail($id);

            // Check ownership
            if ($form->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to sync this form'
                ], 403);
            }

            // Prepare headers
            $headers = ['ID', 'Submitted By', 'Submitted At', 'Created By', 'Google Sheet ID', 'Endpoint'];
            $form->fields->each(function ($field) use (&$headers) {
                $headers[] = $field->question;
            });

            // Prepare data rows
            $rows = [];
            $form->responses->each(function ($response) use ($form, $headers, &$rows) {
                $row = [
                    $response->id,
                    $response->submitter->name ?? 'Anonymous',
                    $response->created_at->toDateTimeString(),
                    $form->creator->name,
                    $form->google_sheet_id,
                    config('app.url') . '/api/public/forms/' . $form->id . '/submit'
                ];

                // Add response data
                foreach (array_slice($headers, 6) as $header) {
                    $row[] = $response->getResponseValue($header) ?? '';
                }

                $rows[] = $row;
            });

            // Update Google Sheet
            $result = $this->googleSheetsService->updateSheet(
                $form->google_sheet_id,
                'Form Responses',
                $headers,
                $rows
            );

            return response()->json([
                'success' => true,
                'message' => 'Google Sheet synced successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Google Sheet sync error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error syncing with Google Sheet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get form responses
     */
    public function getResponses($id)
    {
        try {
            $form = Form::findOrFail($id);

            // Check ownership
            if ($form->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view responses'
                ], 403);
            }

            $responses = $form->responses()
                ->with('submitter')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($response) {
                    return [
                        'id' => $response->id,
                        'responses' => $response->responses,
                        'submitted_by' => $response->submitter->name ?? 'Anonymous',
                        'submitted_at' => $response->created_at->format('M d, Y H:i'),
                        'ip_address' => $response->ip_address,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $responses
            ]);

        } catch (\Exception $e) {
            Log::error('Get form responses error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error loading responses'
            ], 500);
        }
    }

    /**
     * Generate unique Google Sheet ID
     */
    private function generateGoogleSheetId()
    {
        return 'form_' . Str::random(16) . '_' . time();
    }

    /**
     * Initialize Google Sheet structure
     */
    private function initializeGoogleSheet(Form $form)
    {
        try {
            // Prepare headers
            $headers = ['ID', 'Submitted By', 'Submitted At', 'Created By', 'Google Sheet ID', 'Endpoint'];
            $form->fields->each(function ($field) use (&$headers) {
                $headers[] = $field->question;
            });

            // Create sheet with headers
            $result = $this->googleSheetsService->createSheet(
                $form->title . ' Responses',
                $headers
            );

            if ($result['success']) {
                $form->update([
                    'google_sheet_id' => $result['spreadsheetId'],
                    'google_sheet_url' => $result['spreadsheetUrl']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Google Sheet initialization error: ' . $e->getMessage());
        }
    }

    public function indexView()
    {
        return view('form01');
    }

}