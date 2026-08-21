<?php

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ProjectMemberController;
use App\Http\Controllers\Api\V1\ShareController;
use App\Http\Controllers\Api\V1\UtilityController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::middleware(['auth:sanctum', 'active'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::apiResource('projects', ProjectController::class);
        Route::get('projects/{project}/members', [ProjectMemberController::class, 'index']);
        Route::post('projects/{project}/members', [ProjectMemberController::class, 'store']);
        Route::patch('projects/{project}/members/{user}', [ProjectMemberController::class, 'updateRole']);
        Route::delete('projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy']);
        Route::post('projects/{project}/members/accept', [ProjectMemberController::class, 'accept']);
        Route::get('projects/{project}/documents', [DocumentController::class, 'index']);
        Route::post('projects/{project}/documents', [DocumentController::class, 'store']);
        Route::get('documents/{document}', [DocumentController::class, 'show']);
        Route::patch('documents/{document}', [DocumentController::class, 'update']);
        Route::delete('documents/{document}', [DocumentController::class, 'destroy']);
        Route::get('documents/{document}/download', [DocumentController::class, 'download']);
        Route::post('documents/{document}/shares', [ShareController::class, 'store']);
        Route::delete('documents/{document}/shares', [ShareController::class, 'destroy']);
        Route::post('documents/{document}/share-links', [ShareController::class, 'storeLink']);
        Route::delete('share-links/{shareLink}', [ShareController::class, 'destroyLink']);
        Route::get('documents/{document}/comments', [CommentController::class, 'index']);
        Route::post('documents/{document}/comments', [CommentController::class, 'store']);
        Route::patch('comments/{comment}', [CommentController::class, 'update']);
        Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
        Route::get('search', [UtilityController::class, 'search']);
        Route::get('notifications', [UtilityController::class, 'notifications']);
        Route::post('notifications/{id}/read', [UtilityController::class, 'readNotification']);
        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('/', [AdminController::class, 'dashboard']);
            Route::get('users', [AdminController::class, 'users']);
            Route::patch('users/{user}', [AdminController::class, 'updateUser']);
            Route::get('projects', [AdminController::class, 'projects']);
            Route::get('documents', [AdminController::class, 'documents']);
            Route::get('audit-logs', [AdminController::class, 'auditLogs']);
            Route::get('settings', [AdminController::class, 'settings']);
            Route::patch('settings', [AdminController::class, 'updateSettings']);
        });
    });
});
