<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentFieldController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/users', [AuthController::class, 'getUsers'])->middleware('auth:sanctum');
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/business-types', [AuthController::class, 'getBusinessTypes']);
    Route::post('/onboarding/business-type', [AuthController::class, 'setBusinessType']);
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::get('/contacts/search', [ContactController::class, 'search']);
    Route::get('/contacts/{id}', [ContactController::class, 'show']);
    Route::patch('/contacts/{id}', [ContactController::class, 'update']);
    Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);

    Route::middleware(['require.business.type'])->group(function () {
        Route::get('/appointments/form-structure', [AppointmentController::class, 'getFormStructure']);
        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
        Route::patch('/appointments/{id}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);

        Route::get('/appointment-fields', [AppointmentFieldController::class, 'index']);
        Route::post('/appointment-fields', [AppointmentFieldController::class, 'store']);
        Route::put('/appointment-fields/{id}', [AppointmentFieldController::class, 'update']);
        Route::delete('/appointment-fields/{id}', [AppointmentFieldController::class, 'destroy']);
    });
});
