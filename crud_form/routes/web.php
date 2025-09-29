<?php


// Route::get('/', function () {
//     return view('form01');
// });

use Illuminate\Support\Facades\Route;
use App\Services\GoogleSheetsService;

Route::get('/test-sheets', function (GoogleSheetsService $sheets) {
    return response()->json([
        'message' => 'GoogleSheetsService loaded successfully!',
        'service_class' => get_class($sheets)
    ]);
});

Route::get('/', [App\Http\Controllers\FormController::class, 'index']);