<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PtProgramController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\PtPlanController;

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

    // Module 3: Participant Management
    Route::get('participants', [ParticipantController::class, 'index'])->name('admin.participants.index');
    Route::get('participants/export/csv', [ParticipantController::class, 'exportCsv'])->name('admin.participants.export');
    Route::get('participants/{lab}', [ParticipantController::class, 'show'])->name('admin.participants.show');
});
