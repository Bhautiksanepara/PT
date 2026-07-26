<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PtProgramController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\PtPlanController;
use App\Http\Controllers\Admin\SampleBatchController;
use App\Http\Controllers\Admin\SampleAssignmentController;
use App\Http\Controllers\Admin\DispatchController;
use App\Http\Controllers\Admin\ObservationController;
use App\Http\Controllers\Admin\StatisticalEngineController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ArchiveController;
use App\Http\Controllers\Admin\ReferralCodeController;
use App\Http\Controllers\Admin\AdminListExportController;
use App\Http\Controllers\User\UserAuthController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\UserProgramRegistrationController;
use App\Http\Controllers\User\UserReportController;
use App\Http\Controllers\User\UserDispatchController;
use App\Http\Controllers\User\UserObservationController;
use App\Http\Controllers\User\LabProfileController;
use App\Http\Middleware\EnsureLabProfileComplete;

// User / Participant Portal Guest Routes (Registration & Login)
Route::middleware('guest:lab')->group(function () {
    Route::get('/register', [UserAuthController::class, 'showRegisterForm'])->name('user.register');
    Route::post('/register', [UserAuthController::class, 'register'])->name('user.register.submit');
    Route::get('/register/verify', [UserAuthController::class, 'showVerificationForm'])->name('user.register.verify');
    Route::post('/register/verify', [UserAuthController::class, 'verifyEmail'])->name('user.register.verify.submit');
    Route::post('/register/resend-otp', [UserAuthController::class, 'resendVerificationOtp'])->name('user.register.resend-otp');
    Route::get('/login', [UserAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UserAuthController::class, 'login'])->name('user.login.submit');
});

Route::get('/user/login', function() {
    return redirect()->route('login');
})->name('user.login');

// User / Participant Protected Routes
Route::middleware(['auth:lab', EnsureLabProfileComplete::class])->group(function () {
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('user.logout');
    Route::get('/profile/complete', [LabProfileController::class, 'edit'])->name('user.profile.complete');
    Route::put('/profile/complete', [LabProfileController::class, 'update'])->name('user.profile.complete.update');
    Route::get('/profile/change-password', [LabProfileController::class, 'showPasswordForm'])->name('user.password.edit');
    Route::put('/profile/change-password', [LabProfileController::class, 'updatePassword'])->name('user.password.update');
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');

    // Phase 5: Sample Dispatch Tracking & Confirmation Page
    Route::get('/dispatches', [UserDispatchController::class, 'index'])->name('user.dispatches.index');
    Route::post('/dispatches/{registration}/confirm-receipt', [UserDispatchController::class, 'confirmReceipt'])->name('user.dispatches.confirm');
    Route::get('/dispatches/{registration}/slip', [UserDispatchController::class, 'viewPackingSlip'])->name('user.dispatches.slip');

    // Phase 6: Observation Submission Module
    Route::get('/observations', [UserObservationController::class, 'index'])->name('user.observations.index');
    Route::get('/observations/{registration}', [UserObservationController::class, 'showForm'])->name('user.observations.form');
    Route::post('/observations/{registration}', [UserObservationController::class, 'store'])->name('user.observations.store');

    // Aliases for compatibility
    Route::get('/lab/observations', [UserObservationController::class, 'index'])->name('lab.observations.index');
    Route::get('/lab/observations/{registration}', [UserObservationController::class, 'showForm'])->name('lab.observations.form');
    Route::post('/lab/observations/{registration}', [UserObservationController::class, 'store'])->name('lab.observations.store');

    // Phase 3: Program Registration, Referral System & Invoice
    Route::get('/programs/{program}/register', [UserProgramRegistrationController::class, 'showRegisterForm'])->name('user.program.register');
    Route::post('/programs/{program}/register', [UserProgramRegistrationController::class, 'submitRegistration'])->name('user.registration.submit');
    Route::post('/referral/validate', [UserProgramRegistrationController::class, 'validateReferral'])->name('user.referral.validate');
    Route::post('/stripe/create-intent', [UserProgramRegistrationController::class, 'createStripeIntent'])->name('user.stripe.intent');
    Route::get('/registrations/{registration}/invoice', [UserProgramRegistrationController::class, 'viewInvoice'])->name('user.invoice');

    // User Dedicated PT Reports & Certificates Archive
    Route::get('/reports', [UserReportController::class, 'index'])->name('user.reports.index');
    Route::get('/lab/reports', [UserReportController::class, 'index'])->name('lab.reports.index');
    Route::get('/reports/individual/{program}/{registration}', [UserReportController::class, 'viewIndividualReport'])->name('user.reports.individual');
    Route::get('/reports/certificate/{program}/{registration}', [UserReportController::class, 'viewCertificate'])->name('user.reports.certificate');
});

// Redirect root URL to User Login
Route::get('/', function () {
    return redirect()->route('user.login');
});

// Admin Guest Routes (Login Form & Submit)
Route::prefix('admin')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// Admin Protected Routes
Route::prefix('admin')->middleware(App\Http\Middleware\AdminMiddleware::class)->group(function () {
    // Module 1: Admin Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Module 2 & 4: PT Program Management & Registration Window Automation
    Route::get('programs/next-code', [PtProgramController::class, 'getNextCode'])->name('admin.programs.next-code');
    Route::get('programs/export/csv', [AdminListExportController::class, 'programs'])->name('admin.programs.export');
    Route::resource('programs', PtProgramController::class)->names('admin.programs');
    Route::post('programs/{program}/close', [PtProgramController::class, 'close'])->name('admin.programs.close');
    Route::post('programs/{program}/toggle-window', [PtProgramController::class, 'toggleRegistrationStatus'])->name('admin.programs.toggle-window');

    // Module 5: PT Plan Generation
    Route::get('plans', [PtPlanController::class, 'index'])->name('admin.plans.index');
    Route::get('plans/export/csv', [AdminListExportController::class, 'plans'])->name('admin.plans.export');
    Route::get('programs/{program}/plan/create', [PtPlanController::class, 'create'])->name('admin.plans.create');
    Route::post('programs/{program}/plan', [PtPlanController::class, 'store'])->name('admin.plans.store');
    Route::get('programs/{program}/plan', [PtPlanController::class, 'show'])->name('admin.plans.show');
    Route::get('programs/{program}/plan/print', [PtPlanController::class, 'printPlan'])->name('admin.plans.print');

    // Module 6: Sample Production Planning (Batches)
    Route::get('batches', [SampleBatchController::class, 'index'])->name('admin.batches.index');
    Route::get('batches/export/csv', [AdminListExportController::class, 'batches'])->name('admin.batches.export');
    Route::get('batches/create', [SampleBatchController::class, 'create'])->name('admin.batches.create_direct');
    Route::get('programs/{program}/batches/create', [SampleBatchController::class, 'create'])->name('admin.batches.create');
    Route::post('batches', [SampleBatchController::class, 'store'])->name('admin.batches.store');
    Route::get('batches/{batch}', [SampleBatchController::class, 'show'])->name('admin.batches.show');
    Route::get('batches/{batch}/edit', [SampleBatchController::class, 'edit'])->name('admin.batches.edit');
    Route::put('batches/{batch}', [SampleBatchController::class, 'update'])->name('admin.batches.update');
    Route::post('batches/{batch}/approve', [SampleBatchController::class, 'approve'])->name('admin.batches.approve');

    // Module 7: Sample Assignment & Traceability
    Route::get('samples', [SampleAssignmentController::class, 'index'])->name('admin.samples.index');
    Route::get('samples/export/csv', [AdminListExportController::class, 'samples'])->name('admin.samples.export');
    Route::get('programs/{program}/samples', [SampleAssignmentController::class, 'programSamples'])->name('admin.samples.program');
    Route::post('programs/{program}/samples/bulk-assign', [SampleAssignmentController::class, 'bulkAssign'])->name('admin.samples.bulk-assign');
    Route::post('samples/assign-single', [SampleAssignmentController::class, 'assignSingle'])->name('admin.samples.assign-single');

    // Module 8: Dispatch Management
    Route::get('dispatches', [DispatchController::class, 'index'])->name('admin.dispatches.index');
    Route::get('dispatches/export/csv', [AdminListExportController::class, 'dispatches'])->name('admin.dispatches.export');
    Route::get('programs/{program}/dispatches', [DispatchController::class, 'programDispatches'])->name('admin.dispatches.program');
    Route::post('dispatches/single', [DispatchController::class, 'storeSingle'])->name('admin.dispatches.single');
    Route::post('programs/{program}/dispatches/bulk', [DispatchController::class, 'storeBulk'])->name('admin.dispatches.bulk');
    Route::post('dispatches/{dispatch}/resend-notification', [DispatchController::class, 'resendNotification'])->name('admin.dispatches.resend');

    // Mail Test Route
    Route::get('test-email', function () {
        try {
            \Illuminate\Support\Facades\Mail::to('test@apexlabs.com')->send(new \App\Mail\SampleDispatchMail(
                'Apex Analytical Services',
                'PT-2026-001',
                'PT-CHEM-2026-01',
                'BlueDart Express',
                'BD-2026-987654',
                date('Y-m-d'),
                'QR-PT-2026-001'
            ));
            return redirect()->route('admin.dispatches.index')->with('success', 'Test email dispatched successfully! Logged to storage/logs/laravel.log or sent to recipient.');
        } catch (\Exception $e) {
            return redirect()->route('admin.dispatches.index')->with('error', 'Mail Error: ' . $e->getMessage());
        }
    })->name('admin.test-email');

    // Module 9: Observation Management
    Route::get('observations', [ObservationController::class, 'index'])->name('admin.observations.index');
    Route::get('observations/export/csv', [ObservationController::class, 'exportCsv'])->name('admin.observations.export');
    Route::post('observations/bulk-update', [ObservationController::class, 'bulkUpdate'])->name('admin.observations.bulkUpdate');
    Route::get('observations/{observation}', [ObservationController::class, 'show'])->name('admin.observations.show');
    Route::put('observations/{observation}', [ObservationController::class, 'update'])->name('admin.observations.update');

    // Module 10: Statistical Engine (ISO 13528 Standard)
    Route::get('stats', [StatisticalEngineController::class, 'index'])->name('admin.stats.index');
    Route::get('stats/export/csv', [AdminListExportController::class, 'stats'])->name('admin.stats.export-list');
    Route::get('programs/{program}/parameters/{parameter}/stats', [StatisticalEngineController::class, 'parameterStats'])->name('admin.stats.parameter');
    Route::get('programs/{program}/parameters/{parameter}/stats/export', [StatisticalEngineController::class, 'exportCsv'])->name('admin.stats.export');
    Route::post('programs/{program}/freeze-stats', [StatisticalEngineController::class, 'freezeSchemeStats'])->name('admin.stats.freeze');

    // Module 11: Report Generation & Certificates
    Route::get('reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('reports/export/csv', [AdminListExportController::class, 'reports'])->name('admin.reports.export');
    Route::get('programs/{program}/registrations/{registration}/report', [ReportController::class, 'individualReport'])->name('admin.reports.individual');
    Route::get('programs/{program}/registrations/{registration}/certificate', [ReportController::class, 'certificate'])->name('admin.reports.certificate');
    Route::get('programs/{program}/parameters/{parameter}/report', [ReportController::class, 'parameterReport'])->name('admin.reports.parameter');
    Route::get('programs/{program}/master-report', [ReportController::class, 'masterReport'])->name('admin.reports.master');

    // Module 12: Historical Archive
    Route::get('archive', [ArchiveController::class, 'index'])->name('admin.archive.index');
    Route::get('archive/programs/{program}', [ArchiveController::class, 'showProgram'])->name('admin.archive.program');

    // Referral Code Management
    Route::get('referrals', [ReferralCodeController::class, 'index'])->name('admin.referrals.index');
    Route::get('referrals/export/csv', [AdminListExportController::class, 'referrals'])->name('admin.referrals.export');
    Route::post('referrals', [ReferralCodeController::class, 'store'])->name('admin.referrals.store');
    Route::delete('referrals/{referral}', [ReferralCodeController::class, 'destroy'])->name('admin.referrals.destroy');

    // Module 3: Participant Management
    Route::get('participants', [ParticipantController::class, 'index'])->name('admin.participants.index');
    Route::get('participants/export/csv', [ParticipantController::class, 'exportCsv'])->name('admin.participants.export');
    Route::get('participants/{lab}', [ParticipantController::class, 'show'])->name('admin.participants.show');
});
