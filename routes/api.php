<?php

use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\DentalChartController;
use App\Http\Controllers\Api\MedicalHistoryController;
use App\Http\Controllers\Api\DoctorAuthController;
use App\Http\Controllers\Api\DoctorProfileController;
use App\Http\Controllers\Api\PatientAuthController;
use App\Http\Controllers\Api\PatientDashboardController;
use App\Http\Controllers\Api\SecretaryAuthController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminDoctorController;
use App\Http\Controllers\Api\AdminSecretaryController;
use App\Http\Controllers\Api\SecretaryDashboardController;
use App\Http\Controllers\Api\SecretaryAppointmentController;
use App\Http\Controllers\Api\SecretaryDoctorController;
use App\Http\Controllers\Api\SecretaryPatientController;
use App\Http\Controllers\Api\SecretaryPaymentController;
use App\Http\Controllers\Api\SecretaryTreatmentPlanController;
use App\Http\Controllers\Api\AdminPatientController;
use App\Http\Controllers\Api\AdminAppointmentController;
use App\Http\Controllers\Api\AdminTreatmentPlanController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\PatientAppointmentController;
use App\Http\Controllers\Api\PatientTreatmentPlanController;
use App\Http\Controllers\Api\PatientPaymentController;
use App\Http\Controllers\Api\PatientVerificationController;
use App\Http\Controllers\Api\DoctorAppointmentController;
use App\Http\Controllers\Api\DoctorDashboardController;
use App\Http\Controllers\Api\DoctorPatientController;
use App\Http\Controllers\Api\DoctorTreatmentPlanController;
use App\Http\Controllers\Api\DoctorSessionController;
use App\Http\Controllers\Api\MedicationController;
use App\Http\Controllers\Api\PatientMedicationController;
use App\Http\Controllers\Api\DoctorNoteController;
use App\Http\Controllers\Api\DoctorMedicalRecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('patient.bearer:Patient')->get('/patient/me', function (Request $request) {
    return response()->json($request->user());
});

Route::middleware('doctor.bearer:Doctor')->get('/doctor/me', function (Request $request) {
    return response()->json($request->user());
});
//!Authentication Routes  ---------------------------------------------------

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
    Route::get('/medical-history', [MedicalHistoryController::class, 'index'])
        ->middleware('patient.bearer:Patient');
    Route::put('/medical-history', [MedicalHistoryController::class, 'update'])
        ->middleware('patient.bearer:Patient');
    Route::get('/dental-chart', [DentalChartController::class, 'index'])
        ->middleware('patient.bearer:Patient');
    Route::post('/payment/submit', [PatientPaymentController::class, 'submit'])
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
    Route::get('/dashboard', [DoctorDashboardController::class, 'dashboard'])
        ->middleware('doctor.bearer:Doctor');
    Route::get('/patients', [DoctorPatientController::class, 'index'])
        ->middleware('doctor.bearer:Doctor');
});

//! Admin Auth
Route::prefix('admin')->group(function (): void {
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->middleware('admin.bearer:Admin');
    Route::get('/profile', [AdminAuthController::class, 'profile'])
        ->middleware('admin.bearer:Admin');
    Route::post('/update-profile', [AdminAuthController::class, 'updateProfile'])
        ->middleware('admin.bearer:Admin');
});
//! seceretary Auth
Route::prefix('secretary')->group(function (): void {
    Route::post('/login', [SecretaryAuthController::class, 'login']);
    Route::post('/logout', [SecretaryAuthController::class, 'logout'])
        ->middleware('secretary.bearer:Secretary');
    Route::get('/profile', [SecretaryAuthController::class, 'profile'])
        ->middleware('secretary.bearer:Secretary');
    Route::post('/update-profile', [SecretaryAuthController::class, 'updateProfile'])
        ->middleware('secretary.bearer:Secretary');
    Route::get('/dashboard', [SecretaryDashboardController::class, 'dashboard'])
        ->middleware('secretary.bearer:Secretary');
    Route::get('/appointments', [SecretaryAppointmentController::class, 'index'])
        ->middleware('secretary.bearer:Secretary');
    Route::get('/appointments/{appointment}', [SecretaryAppointmentController::class, 'show'])
        ->middleware('secretary.bearer:Secretary');
    Route::put('/appointments/{appointment}', [SecretaryAppointmentController::class, 'update'])
        ->middleware('secretary.bearer:Secretary');
    Route::post('/appointments/treatment-plans/{plan}/stages/{stage}', [SecretaryAppointmentController::class, 'storeStage'])
        ->middleware('secretary.bearer:Secretary');
    Route::get('/patients', [SecretaryPatientController::class, 'index'])
        ->middleware('secretary.bearer:Secretary');
    Route::get('/patients/{patient}', [SecretaryPatientController::class, 'show'])
        ->middleware('secretary.bearer:Secretary');
    Route::get('/patients/{patient}/invoices', [SecretaryPaymentController::class, 'pendingInvoices'])
        ->middleware('secretary.bearer:Secretary,patient');
    Route::post('/patients/{patient}/payments', [SecretaryPaymentController::class, 'store'])
        ->middleware('secretary.bearer:Secretary');
    Route::get('/doctors', [SecretaryDoctorController::class, 'index'])
        ->middleware('secretary.bearer:Secretary');
    Route::get('/treatment-plans', [SecretaryTreatmentPlanController::class, 'index'])
        ->middleware('secretary.bearer:Secretary');
    Route::get('/treatment-plans/{treatmentPlan}', [SecretaryTreatmentPlanController::class, 'show'])
        ->middleware('secretary.bearer:Secretary');
});
//! Admin manage dashboard
Route::prefix('admin')->group(function (): void {
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])
        ->middleware('admin.bearer:Admin');

    //! Admin manage doctors
    Route::get('/doctors', [AdminDoctorController::class, 'index'])
        ->middleware('admin.bearer:Admin');
    Route::post('/doctors', [AdminDoctorController::class, 'store'])
        ->middleware('admin.bearer:Admin');
    Route::get('/doctors/{doctor}', [AdminDoctorController::class, 'show'])
        ->middleware('admin.bearer:Admin');
    Route::put('/doctors/{doctor}', [AdminDoctorController::class, 'update'])
        ->middleware('admin.bearer:Admin');
    Route::delete('/doctors/{doctor}', [AdminDoctorController::class, 'destroy'])
        ->middleware('admin.bearer:Admin');
    //! Admin manage secretary (CRUD)
    Route::get('/secretaries', [AdminSecretaryController::class, 'index'])
        ->middleware('admin.bearer:Admin');
    Route::post('/secretaries', [AdminSecretaryController::class, 'store'])
        ->middleware('admin.bearer:Admin');
    Route::put('/secretaries/{secretary}', [AdminSecretaryController::class, 'update'])
        ->middleware('admin.bearer:Admin');
    Route::delete('/secretaries/{secretary}', [AdminSecretaryController::class, 'destroy'])
        ->middleware('admin.bearer:Admin');
    //! Admin manage patients
    Route::get('/patients', [AdminPatientController::class, 'index'])
        ->middleware('admin.bearer:Admin');
    Route::post('/patients', [AdminPatientController::class, 'store'])
        ->middleware('admin.bearer:Admin');
    Route::get('/patients/{patient}', [AdminPatientController::class, 'show'])
        ->middleware('admin.bearer:Admin');
    Route::put('/patients/{patient}', [AdminPatientController::class, 'update'])
        ->middleware('admin.bearer:Admin');
    Route::delete('/patients/{patient}', [AdminPatientController::class, 'destroy'])
        ->middleware('admin.bearer:Admin');
    Route::get('/{id}/points', [PatientDashboardController::class, 'points'])
        ->middleware('patient.bearer:Admin');
    //! Admin manage appointments (Read only)
    Route::get('/appointments', [AdminAppointmentController::class, 'index'])
        ->middleware('admin.bearer:Admin');
    Route::get('/appointments/{appointment}', [AdminAppointmentController::class, 'show'])
        ->middleware('admin.bearer:Admin');
    //! Admin manage treatment plans(Read only)
    Route::get('/treatment-plans', [AdminTreatmentPlanController::class, 'index'])
        ->middleware('admin.bearer:Admin');
    Route::get('/treatment-plans/{treatmentPlan}', [AdminTreatmentPlanController::class, 'show'])
        ->middleware('admin.bearer:Admin');
});



//!  patient & Doctors
Route::get('/doctors', [PatientDashboardController::class, 'allDoctors']);

//! Appointment booking & availability
Route::prefix('appointments')->group(function (): void {
    Route::post('/diagnostic', [AppointmentController::class, 'createDiagnostic']);
    Route::get('/diagnostic/busy', [AppointmentController::class, 'diagnosticBusySlots']);
    Route::post('/normal', [AppointmentController::class, 'createNormal']);
});
Route::get('/doctors/{id}/availability', [AppointmentController::class, 'doctorAvailability']);
Route::get('/doctors/{id}/busy', [AppointmentController::class, 'doctorBusySlots']);

//! Doctor verifies a patient's profile (medical record + allergies)
//! after their first (diagnostic) appointment.
Route::post('/doctor/patients/{patient}/verify', [PatientVerificationController::class, 'verify'])
    ->middleware('doctor.bearer:Doctor');

//! patient appointments
Route::prefix('patient/appointments')->group(function (): void {

    Route::post('/add', [PatientAppointmentController::class, 'addAppointment'])
        ->middleware('patient.bearer:Patient');
    Route::post('/{appointment}/cancel', [PatientAppointmentController::class, 'cancelAppointment'])
        ->middleware('patient.bearer:Patient');
    Route::get('/{appointment}', [PatientAppointmentController::class, 'showAppointmentDetails'])
        ->middleware('patient.bearer:Patient');
    Route::get('/', [PatientAppointmentController::class, 'showAllAppointments'])
        ->middleware('patient.bearer:Patient');
    Route::get('/upcoming', [PatientDashboardController::class, 'upcomingAppointments'])
        ->middleware('patient.bearer:Patient');
});
//! patient Tretment plan
Route::prefix('patient/treatment-plan')->group(function (): void {
    Route::get('/', [PatientTreatmentPlanController::class, 'showTreatmentPlans'])
        ->middleware('patient.bearer:Patient');
    Route::get('/{treatmentPlan}', [PatientTreatmentPlanController::class, 'showTreatmentPlanDetails'])
        ->middleware('patient.bearer:Patient');
});
//! doctor appointments
Route::prefix('doctor/appointments')->group(function (): void {
    Route::get('/', [DoctorAppointmentController::class, 'showDoctorAppointments'])
        ->middleware('doctor.bearer:Doctor');
    Route::get('/upcoming', [DoctorAppointmentController::class, 'showDoctorUpcomingAppointments'])
        ->middleware('doctor.bearer:Doctor');
    Route::get('/{appointment}', [DoctorAppointmentController::class, 'showDoctorAppointmentDetails'])
        ->middleware('doctor.bearer:Doctor');
});

//! Doctor treatment-plan management (Phase B)
Route::prefix('doctor')->middleware('doctor.bearer:Doctor')->group(function (): void {
    Route::get('/patients/{patient}/treatment-plans', [DoctorTreatmentPlanController::class, 'index']);
    Route::get('/treatment-plans', [DoctorTreatmentPlanController::class, 'indexAll']);
    Route::get('/treatment-plans/{plan}', [DoctorTreatmentPlanController::class, 'show']);
    Route::post('/treatment-plans', [DoctorTreatmentPlanController::class, 'store']);
    Route::post('/treatment-plans/{plan}/stages', [DoctorTreatmentPlanController::class, 'storeStage']);
    Route::post('/treatment-plans/{plan}/stages/{stage}/appointments', [DoctorTreatmentPlanController::class, 'storeStageAppointment']);
    Route::get('/treatment-plans/{plan}/dental-chart', [DoctorTreatmentPlanController::class, 'chart']);
    Route::put('/treatment-plans/{plan}/dental-chart/teeth/{tooth}', [DoctorTreatmentPlanController::class, 'updateChartTooth']);

    //! Phase F — session lifecycle + plan status
    Route::post('/appointments/{appointment}/start', [DoctorSessionController::class, 'start']);
    Route::post('/appointments/{appointment}/complete', [DoctorSessionController::class, 'complete']);
    Route::post('/treatment-plans/{plan}/stages/{stage}/done', [DoctorSessionController::class, 'markStageDone']);
    Route::post('/treatment-plans/{plan}/finish', [DoctorSessionController::class, 'finishPlan']);
    Route::post('/treatment-plans/{plan}/cancel', [DoctorSessionController::class, 'cancelPlan']);

    //! Phase I — doctor medical-record aggregate + CRUD
    Route::get('/patients/{patient}/medical-record', [DoctorMedicalRecordController::class, 'show']);
    Route::post('/patients/{patient}/medical-record/allergies', [DoctorMedicalRecordController::class, 'storeAllergy']);
    Route::put('/patients/{patient}/medical-record/allergies/{allergy}', [DoctorMedicalRecordController::class, 'updateAllergy']);
    Route::delete('/patients/{patient}/medical-record/allergies/{allergy}', [DoctorMedicalRecordController::class, 'destroyAllergy']);
    Route::post('/patients/{patient}/medical-record/history', [DoctorMedicalRecordController::class, 'storeHistory']);
    Route::put('/patients/{patient}/medical-record/history/{history}', [DoctorMedicalRecordController::class, 'updateHistory']);
    Route::delete('/patients/{patient}/medical-record/history/{history}', [DoctorMedicalRecordController::class, 'destroyHistory']);
    Route::post('/patients/{patient}/medical-record/diagnoses', [DoctorMedicalRecordController::class, 'storeDiagnosis']);
    Route::put('/patients/{patient}/medical-record/diagnoses/{diagnosis}', [DoctorMedicalRecordController::class, 'updateDiagnosis']);
    Route::delete('/patients/{patient}/medical-record/diagnoses/{diagnosis}', [DoctorMedicalRecordController::class, 'destroyDiagnosis']);
});

//! Medication catalog (admin manages, everyone reads)
Route::prefix('medications')->group(function (): void {
    Route::get('/', [MedicationController::class, 'index']);
    Route::get('/{medication}', [MedicationController::class, 'show']);
    Route::post('/', [MedicationController::class, 'store'])
        ->middleware('admin.bearer:Admin');
    Route::put('/{medication}', [MedicationController::class, 'update'])
        ->middleware('admin.bearer:Admin');
    Route::delete('/{medication}', [MedicationController::class, 'destroy'])
        ->middleware('admin.bearer:Admin');
});

//! Patient current medications (doctor prescribes, patient reads)
Route::prefix('patients/{patient}/medications')->group(function (): void {
    Route::get('/', [PatientMedicationController::class, 'index'])
        ->middleware('doctor.bearer:Doctor,Patient');
    Route::get('/current', [PatientMedicationController::class, 'current'])
        ->middleware('doctor.bearer:Doctor,Patient');
    Route::post('/', [PatientMedicationController::class, 'store'])
        ->middleware('doctor.bearer:Doctor');
    Route::put('/{patientMedication}', [PatientMedicationController::class, 'update'])
        ->middleware('doctor.bearer:Doctor');
    Route::delete('/{patientMedication}', [PatientMedicationController::class, 'destroy'])
        ->middleware('doctor.bearer:Doctor');
});

//! Doctor notes (doctors write notes on a patient's medical record)
Route::prefix('patients/{patient}/notes')->group(function (): void {
    Route::get('/', [DoctorNoteController::class, 'index'])
        ->middleware('doctor.bearer:Doctor,Patient');
    Route::post('/', [DoctorNoteController::class, 'store'])
        ->middleware('doctor.bearer:Doctor');
    Route::get('/{note}', [DoctorNoteController::class, 'show'])
        ->middleware('doctor.bearer:Doctor,Patient');
    Route::delete('/{note}', [DoctorNoteController::class, 'destroy'])
        ->middleware('doctor.bearer:Doctor');
});
