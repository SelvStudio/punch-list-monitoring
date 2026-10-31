<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PunchListController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Punch Lists (Create / Store must come before {punchList})
    Route::middleware('role:admin,management')->group(function () {
        Route::get('/punch-lists/create', [PunchListController::class, 'create'])->name('punch-lists.create');
        Route::post('/punch-lists', [PunchListController::class, 'store'])->name('punch-lists.store');
        Route::get('/punch-lists/{punchList}/edit', [PunchListController::class, 'edit'])->name('punch-lists.edit');
        Route::put('/punch-lists/{punchList}', [PunchListController::class, 'update'])->name('punch-lists.update');
    });

    Route::middleware('role:admin')->group(function () {
        Route::delete('/punch-lists/{punchList}', [PunchListController::class, 'destroy'])->name('punch-lists.destroy');
    });

    Route::get('/punch-lists', [PunchListController::class, 'index'])->name('punch-lists.index');
    Route::get('/punch-lists/{punchList}', [PunchListController::class, 'show'])->name('punch-lists.show');
    Route::post('/punch-lists/{punchList}/status', [PunchListController::class, 'updateStatus'])->name('punch-lists.status.update');
    Route::post('/punch-lists/{punchList}/comments', [PunchListController::class, 'addComment'])->name('punch-lists.comments.store');
    Route::post('/punch-lists/{punchList}/documents', [PunchListController::class, 'uploadDocument'])->name('punch-lists.documents.store');
    Route::delete('/documents/{document}', [PunchListController::class, 'deleteDocument'])->name('documents.destroy');

    // Monitoring
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::get('/reports/print', [ReportController::class, 'printView'])->name('reports.print');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Activity Logs (Admin & Management)
    Route::middleware('role:admin,management')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // Admin Only: User Management & System Settings
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/trigger-check', [SettingController::class, 'triggerDeadlineCheck'])->name('settings.trigger-check');
    });
});
