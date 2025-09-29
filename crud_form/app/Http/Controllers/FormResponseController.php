<?php
namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormResponse;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FormResponseController extends Controller
{
    protected $googleSheetsService;

    public function __construct(GoogleSheetsService $googleSheetsService)
    {
        $this->googleSheetsService = $googleSheetsService;
    }

    /**
     * Submit form response (public endpoint)
     */
    public function store(Request $request, $formId)
    {
        try {
            $form = Form::with('fields')->active()->findOrFail($formId);

            // Build validation rules from form fields
            $rules = [];
            $fieldQuestions = [];
            
            foreach ($form->fields as $field) {
                $rules[$field->question] = $field->validation_rules;
                $fieldQuestions[] = $field->question;
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Prepare response data
            $responses = $request->only($fieldQuestions);

            // Create form response
            $formResponse = FormResponse::create([
                'form_id' => $form->id,
                'responses' => $responses,
                'submitted_by' => Auth::check() ? Auth::id() : null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Sync to Google Sheet
            $this->syncSingleResponseToGoogleSheet($form, $formResponse);

            return response()->json([
                'success' => true,
                'message' => 'Form submitted successfully',
                'response_id' => $formResponse->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting form'
            ], 500);
        }
    }

    /**
     * Get form responses for authenticated user
     */
    public function index($formId)
    {
        try {
            $form = Form::findOrFail($formId);

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
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $responses
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading responses'
            ], 500);
        }
    }

    /**
     * Delete form response
     */
    public function destroy($id)
    {
        try {
            $response = FormResponse::with('form')->findOrFail($id);

            // Check ownership
            if ($response->form->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this response'
                ], 403);
            }

            $response->delete();

            return response()->json([
                'success' => true,
                'message' => 'Response deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting response'
            ], 500);
        }
    }

    /**
     * Export form responses
     */
    public function export($formId)
    {
        try {
            $form = Form::with(['fields', 'responses'])->findOrFail($formId);

            // Check ownership
            if ($form->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to export responses'
                ], 403);
            }

            $csvData = $this->generateCSV($form);

            return response($csvData)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $form->title . '_responses.csv"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting responses'
            ], 500);
        }
    }

    /**
     * Sync single response to Google Sheet
     */
    private function syncSingleResponseToGoogleSheet(Form $form, FormResponse $response)
    {
        try {
            if (!$form->google_sheet_id) {
                return;
            }

            $headers = ['ID', 'Submitted By', 'Submitted At', 'Created By', 'Google Sheet ID', 'Endpoint'];
            $form->fields->each(function ($field) use (&$headers) {
                $headers[] = $field->question;
            });

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

            $this->googleSheetsService->appendToSheet(
                $form->google_sheet_id,
                'Form Responses!A:A',
                $row
            );

        } catch (\Exception $e) {
            // Log error but don't fail the form submission
            \Log::error('Single response Google Sheet sync error: ' . $e->getMessage());
        }
    }

    /**
     * Generate CSV data for export
     */
    private function generateCSV(Form $form)
    {
        $headers = ['Submission ID', 'Submitted By', 'Submitted At'];
        $form->fields->each(function ($field) use (&$headers) {
            $headers[] = $field->question;
        });

        $output = fopen('php://output', 'w');
        ob_start();
        fputcsv($output, $headers);

        $form->responses->each(function ($response) use ($output, $form) {
            $row = [
                $response->id,
                $response->submitter->name ?? 'Anonymous',
                $response->created_at->toDateTimeString()
            ];

            $form->fields->each(function ($field) use (&$row, $response) {
                $row[] = $response->getResponseValue($field->question) ?? '';
            });

            fputcsv($output, $row);
        });

        fclose($output);
        return ob_get_clean();
    }
}