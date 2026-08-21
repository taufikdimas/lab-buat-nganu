<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PublicShareController;
use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\AuditLogTable;
use App\Livewire\Admin\DocumentTable;
use App\Livewire\Admin\ProjectTable;
use App\Livewire\Admin\SystemSettingsForm;
use App\Livewire\Admin\UserTable;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Documents\DocumentDetail;
use App\Livewire\Notifications\NotificationList;
use App\Livewire\Profile\ProfileForm;
use App\Livewire\Projects\ProjectDetail;
use App\Livewire\Projects\ProjectForm;
use App\Livewire\Projects\ProjectList;
use App\Livewire\Search\GlobalSearch;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome')->name('home');
Route::get('share/{token}', [PublicShareController::class, 'show'])->name('share.show');
Route::get('share/{token}/download', [PublicShareController::class, 'download'])->name('share.download');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('projects', ProjectList::class)->name('projects.index');
    Route::get('projects/create', ProjectForm::class)->name('projects.create');
    Route::get('projects/{project}', ProjectDetail::class)->name('projects.show');
    Route::get('projects/{project}/documents/{document}', DocumentDetail::class)->scopeBindings()->name('documents.show');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('search', GlobalSearch::class)->name('search');
    Route::get('notifications', NotificationList::class)->name('notifications');
    Route::get('profile', ProfileForm::class)->name('profile');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboard::class)->name('index');
        Route::get('/users', UserTable::class)->name('users');
        Route::get('/audit-logs', AuditLogTable::class)->name('audit-logs');
        Route::get('/settings', SystemSettingsForm::class)->name('settings');
        Route::get('/projects', ProjectTable::class)->name('projects');
        Route::get('/documents', DocumentTable::class)->name('documents');
    });

    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
