<?php

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