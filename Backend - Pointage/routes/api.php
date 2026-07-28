<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\DailyReportController;
use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;

// --- Routes publiques ---
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/dg-exists', [AuthController::class, 'checkDgExists']);
Route::post('/auth/bootstrap-dg', [AuthController::class, 'bootstrapDg']);

// --- Routes authentifiées (employé ou DG) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/check-ins', [CheckInController::class, 'index']);
    Route::get('/check-ins/today', [CheckInController::class, 'today']);
    Route::post('/check-ins', [CheckInController::class, 'store']);

    Route::get('/daily-reports', [DailyReportController::class, 'index']);
    Route::post('/daily-reports', [DailyReportController::class, 'store']);

    // --- Routes réservées au DG ---
    // IMPORTANT : ces routes littérales ("/all") doivent être déclarées
    // AVANT la route à paramètre "/daily-reports/{dailyReport}" plus bas,
    // sinon Laravel matche "all" comme un {dailyReport} et renvoie 404.
    Route::middleware('dg')->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::post('/employees', [EmployeeController::class, 'store']);
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);

        Route::get('/check-ins/all', [CheckInController::class, 'all']);
        Route::get('/daily-reports/all', [DailyReportController::class, 'all']);
    });

    Route::get('/daily-reports/{dailyReport}', [DailyReportController::class, 'show']);
});
