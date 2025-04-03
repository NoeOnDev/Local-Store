<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentFieldController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/users', [AuthController::class, 'getUsers'])->middleware('auth:sanctum');
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/business-types', [AuthController::class, 'getBusinessTypes']);
    Route::post('/onboarding/custom-fields', [AuthController::class, 'setCustomFields']);
    Route::post('/onboarding/business-type', [AuthController::class, 'setBusinessType']);
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::get('/contacts/search', [ContactController::class, 'search']);
    Route::get('/contacts/{id}', [ContactController::class, 'show']);
    Route::patch('/contacts/{id}', [ContactController::class, 'update']);
    Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);

    Route::middleware(['require.business.type'])->group(function () {
        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::get('/appointments/search', [AppointmentController::class, 'search']);
        Route::get('/appointments/export-columns', [AppointmentController::class, 'getExportColumns']);
        Route::get('/appointments/export', [AppointmentController::class, 'exportCsv']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
        Route::patch('/appointments/{id}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);

        Route::get('/appointment-fields', [AppointmentFieldController::class, 'index']);
        Route::post('/appointment-fields', [AppointmentFieldController::class, 'store']);
        Route::put('/appointment-fields/{id}', [AppointmentFieldController::class, 'update']);
        Route::delete('/appointment-fields/{id}', [AppointmentFieldController::class, 'destroy']);
        Route::post('/appointments/{id}/attend', [AppointmentController::class, 'attend']);
    });

    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::post('/profile/image', [AuthController::class, 'updateProfileImage']);
});
