<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectorDecisionController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventReportController;
use App\Http\Controllers\EventVerificationController;
use App\Http\Controllers\MarketingLinkController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesUpdateController;
use App\Http\Controllers\SurveyController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Survey Routes (Peserta Event / Customer)
Route::get('/survei/{token}', [SurveyController::class, 'show'])->name('survey.show');
Route::post('/survei/{token}', [SurveyController::class, 'store'])->middleware('throttle:15,1')->name('survey.store');
Route::get('/auth/google/survey/{token}', [SurveyController::class, 'redirectToGoogle'])->name('survey.google');
Route::get('/auth/google/callback', [SurveyController::class, 'handleGoogleCallback'])->name('survey.google.callback');

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// Dashboard & Evaluasi Internal
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');
Route::get('/dashboard/print/{event}', [DashboardController::class, 'print'])->middleware(['auth'])->name('dashboard.print');

// Internal Authenticated Routes
Route::middleware('auth')->group(function () {
    // Events - Pendaftaran, Rencana Event & Ekspor Data
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/export', [EventController::class, 'export'])->name('events.export');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');

    // Events - Laporan Realisasi Pasca-Event
    Route::get('/events/{event}/report', [EventReportController::class, 'edit'])->name('events.report.edit');
    Route::post('/events/{event}/report', [EventReportController::class, 'update'])->name('events.report.update');

    // Events - Pembaruan Omzet H+90 (Khusus Event Sales)
    Route::get('/events/{event}/sales', [SalesUpdateController::class, 'edit'])->name('events.sales.edit');
    Route::post('/events/{event}/sales', [SalesUpdateController::class, 'update'])->name('events.sales.update');
    Route::get('/events/{event}/invoice', [SalesUpdateController::class, 'downloadInvoice'])->name('events.invoice.download');
    Route::get('/events/{event}/proposal', [EventController::class, 'downloadProposal'])->name('events.proposal.download');
    Route::get('/events/{event}/attendance', [EventReportController::class, 'downloadAttendance'])->name('events.attendance.download');
    Route::get('/events/{event}/documentation', [EventReportController::class, 'downloadDocumentation'])->name('events.documentation.download');

    // Events - Verifikasi Marketing & Keputusan Direksi
    Route::post('/events/{event}/verify', [EventVerificationController::class, 'update'])->name('events.verify');
    Route::post('/events/{event}/decision', [DirectorDecisionController::class, 'update'])->name('events.decision');

    // In-App Notifications
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Marketing & Admin Links & QR Management
Route::middleware(['auth', 'role:marketing|admin'])->prefix('marketing')->name('marketing.')->group(function () {
    Route::get('/links', [MarketingLinkController::class, 'index'])->name('links.index');
    Route::get('/links/{event}/qr', [MarketingLinkController::class, 'downloadQr'])->name('links.downloadQr');
    Route::get('/links/{event}/survey-responses', [MarketingLinkController::class, 'exportSurveyResponses'])->name('links.surveyResponses');
});

// Admin Management (Users, Branches, Activity Logs)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Kelola Pengguna
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Kelola Kantor Cabang
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

    // Audit Trail / Log Aktivitas
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});

require __DIR__.'/auth.php';
