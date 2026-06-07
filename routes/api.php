<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\PatientController;
use Illuminate\Support\Facades\Route;

Route::apiResource('doctors', DoctorController::class);
Route::apiResource('patients', PatientController::class);

Route::post(
    'availabilities',
    [AvailabilityController::class, 'store']
);

Route::get(
    'doctors/{doctor}/availabilities',
    [AvailabilityController::class, 'index']
);

Route::post('appointments', [AppointmentController::class, 'store']);

Route::patch('appointments/{appointment}/confirm', [AppointmentController::class, 'confirm']);
Route::patch('appointments/{appointment}/complete', [AppointmentController::class, 'complete']);
Route::patch('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);

Route::get('patients/{patient}/appointments', [AppointmentController::class, 'patientAppointments']);