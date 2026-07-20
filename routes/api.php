<?php

use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\DoctorAuthController;
use App\Http\Controllers\Api\DoctorProfileController;
use App\Http\Controllers\Api\PatientAuthController;
use App\Http\Controllers\Api\PatientDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('patient.bearer:Patient')->get('/patient/me', function (Request $request) {
    return response()->json($request->user());
});

Route::middleware('doctor.bearer:Doctor')->get('/doctor/me', function (Request $request) {
    return response()->json($request->user());
});
//!Authentication Routes

//! Patient
Route::prefix('patient')->group(function (): void {
    Route::post('/register', [PatientAuthController::class, 'register']);
    Route::post('/login', [PatientAuthController::class, 'login']);
    Route::post('/logout', [PatientAuthController::class, 'logout'])
        ->middleware('patient.bearer:Patient');
    Route::get('/profile', [PatientAuthController::class, 'profile'])
        ->middleware('patient.bearer:Patient');
    Route::post('/update-profile', [PatientAuthController::class, 'updateProfile'])
        ->middleware('patient.bearer:Patient');
    Route::get('/doctors/{doctor}', [DoctorProfileController::class, 'show'])
        ->middleware('patient.bearer:Patient');
    Route::get('/{id}/dashboard', [PatientDashboardController::class, 'dashboard'])
        ->middleware('patient.bearer:Patient');
    Route::get('/{id}/appointments/upcoming', [PatientDashboardController::class, 'upcomingAppointments'])
        ->middleware('patient.bearer:Patient');
    Route::get('/{id}/points', [PatientDashboardController::class, 'points'])
        ->middleware('patient.bearer:Patient');
    Route::get('/{id}/progress', [PatientDashboardController::class, 'progress'])
        ->middleware('patient.bearer:Patient');
});
//! Doctor
Route::prefix('doctor')->group(function (): void {
    Route::post('/register', [DoctorAuthController::class, 'register']);
    Route::post('/login', [DoctorAuthController::class, 'login']);
    Route::post('/logout', [DoctorAuthController::class, 'logout'])
        ->middleware('doctor.bearer:Doctor');
    Route::get('/profile', [DoctorAuthController::class, 'profile'])
        ->middleware('doctor.bearer:Doctor');
    Route::post('/update-profile', [DoctorAuthController::class, 'updateProfile'])
        ->middleware('doctor.bearer:Doctor');
    Route::post('/cases/{case}/finish', [CaseController::class, 'finish'])
        ->middleware('doctor.bearer:Doctor');
});

//! Dashboard & Doctors
Route::get('/doctors', [PatientDashboardController::class, 'allDoctors']);
