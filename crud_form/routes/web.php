<?php


// Route::get('/', function () {
//     return view('form01');
// });

use Illuminate\Support\Facades\Route;
use App\Services\GoogleSheetsService;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\FormController;

Route::get('/test-sheets', function (GoogleSheetsService $sheets) {
    return response()->json([
        'message' => 'GoogleSheetsService loaded successfully!',
        'service_class' => get_class($sheets)
    ]);
});

Route::get('/', [LoginController::class, 'index']);
Route::get('/login', [LoginController::class, 'index'])->name('login.show');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::get('/form', [FormController::class, 'index'])->name('dashboard');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');