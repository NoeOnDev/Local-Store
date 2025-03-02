<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CitationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/users', [AuthController::class, 'getUsers'])->middleware('auth:sanctum');
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::get('/contacts/search', [ContactController::class, 'search']);
    Route::get('/contacts/{id}', [ContactController::class, 'show']);
    Route::patch('/contacts/{id}', [ContactController::class, 'update']);
    Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);

    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    Route::patch('/appointments/{id}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);
});

Route::post('/citas/creartabla', [CitationController::class, 'createTable']);
Route::post('/citas/{nameTable}', [CitationController::class, 'addValues']);
Route::get('/citas/{nameTable}', [CitationController::class, 'getValues']);
Route::get('/citas/{nameTable}/{id}', [CitationController::class, 'getByIdValues']);
Route::put('/citas/{nameTable}/{id}', [CitationController::class, 'updateValues']);
Route::delete('/citas/{nameTable}/{id}', [CitationController::class, 'deleteValues']);
Route::delete('/citas/{nameTable}', [CitationController::class, 'deleteTable']);
