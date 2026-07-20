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

// Redirect root URL to Admin Login / Dashboard
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
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
    Route::resource('programs', PtProgramController::class)->names('admin.programs');
    Route::post('programs/{program}/close', [PtProgramController::class, 'close'])->name('admin.programs.close');
    Route::post('programs/{program}/toggle-window', [PtProgramController::class, 'toggleRegistrationStatus'])->name('admin.programs.toggle-window');

    // Module 5: PT Plan Generation
    Route::get('plans', [PtPlanController::class, 'index'])->name('admin.plans.index');
    Route::get('programs/{program}/plan/create', [PtPlanController::class, 'create'])->name('admin.plans.create');
    Route::post('programs/{program}/plan', [PtPlanController::class, 'store'])->name('admin.plans.store');
    Route::get('programs/{program}/plan', [PtPlanController::class, 'show'])->name('admin.plans.show');
    Route::get('programs/{program}/plan/print', [PtPlanController::class, 'printPlan'])->name('admin.plans.print');

    // Module 6: Sample Production Planning (Batches)
    Route::get('batches', [SampleBatchController::class, 'index'])->name('admin.batches.index');
    Route::get('batches/create', [SampleBatchController::class, 'create'])->name('admin.batches.create_direct');
    Route::get('programs/{program}/batches/create', [SampleBatchController::class, 'create'])->name('admin.batches.create');
    Route::post('batches', [SampleBatchController::class, 'store'])->name('admin.batches.store');
    Route::get('batches/{batch}', [SampleBatchController::class, 'show'])->name('admin.batches.show');
    Route::get('batches/{batch}/edit', [SampleBatchController::class, 'edit'])->name('admin.batches.edit');
    Route::put('batches/{batch}', [SampleBatchController::class, 'update'])->name('admin.batches.update');
    Route::post('batches/{batch}/approve', [SampleBatchController::class, 'approve'])->name('admin.batches.approve');

    // Module 7: Sample Assignment & Traceability
    Route::get('samples', [SampleAssignmentController::class, 'index'])->name('admin.samples.index');
    Route::get('programs/{program}/samples', [SampleAssignmentController::class, 'programSamples'])->name('admin.samples.program');
    Route::post('programs/{program}/samples/bulk-assign', [SampleAssignmentController::class, 'bulkAssign'])->name('admin.samples.bulk-assign');
    Route::post('samples/assign-single', [SampleAssignmentController::class, 'assignSingle'])->name('admin.samples.assign-single');

    // Module 8: Dispatch Management
    Route::get('dispatches', [DispatchController::class, 'index'])->name('admin.dispatches.index');
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
    Route::get('observations/{observation}', [ObservationController::class, 'show'])->name('admin.observations.show');
    Route::put('observations/{observation}', [ObservationController::class, 'update'])->name('admin.observations.update');

    // Module 10: Statistical Engine (ISO 13528 Standard)
    Route::get('stats', [StatisticalEngineController::class, 'index'])->name('admin.stats.index');
    Route::get('programs/{program}/parameters/{parameter}/stats', [StatisticalEngineController::class, 'parameterStats'])->name('admin.stats.parameter');
    Route::get('programs/{program}/parameters/{parameter}/stats/export', [StatisticalEngineController::class, 'exportCsv'])->name('admin.stats.export');

    // Module 11: Report Generation & Certificates
    Route::get('reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('programs/{program}/registrations/{registration}/report', [ReportController::class, 'individualReport'])->name('admin.reports.individual');
    Route::get('programs/{program}/registrations/{registration}/certificate', [ReportController::class, 'certificate'])->name('admin.reports.certificate');
    Route::get('programs/{program}/parameters/{parameter}/report', [ReportController::class, 'parameterReport'])->name('admin.reports.parameter');
    Route::get('programs/{program}/master-report', [ReportController::class, 'masterReport'])->name('admin.reports.master');

    // Module 12: Historical Archive
    Route::get('archive', [ArchiveController::class, 'index'])->name('admin.archive.index');
    Route::get('archive/programs/{program}', [ArchiveController::class, 'showProgram'])->name('admin.archive.program');

    // Module 3: Participant Management
    Route::get('participants', [ParticipantController::class, 'index'])->name('admin.participants.index');
    Route::get('participants/export/csv', [ParticipantController::class, 'exportCsv'])->name('admin.participants.export');
    Route::get('participants/{lab}', [ParticipantController::class, 'show'])->name('admin.participants.show');
});
