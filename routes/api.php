<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum', 'throttle:60,1')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', MeController::class);

    Route::get('/auth/user', [AuthController::class, 'user']);

    Route::get('/users', [UserController::class, 'index']);
    Route::prefix('/user')->group(function () {
        Route::put('/{id}', [UserController::class, 'update']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::delete('/{user}', [UserController::class, 'destroy']);

        Route::post('/{id}/reset-password', [UserController::class, 'resetPassword']);
    });

    Route::prefix('activitylogs')->group(function () {
        Route::get('/all', [ActivityLogController::class, 'getAllRecords']);
        Route::get('/', [ActivityLogController::class, 'index']);
    });

    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::prefix('/department')->group(function () {
        Route::put('/{id}', [DepartmentController::class, 'update']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('/{department}', [DepartmentController::class, 'show']);
        Route::delete('/{department}', [DepartmentController::class, 'destroy']);
    });

    Route::get('/folders', [FolderController::class, 'index']);
    Route::prefix('/folder')->group(function () {
        Route::put('/{id}', [FolderController::class, 'update']);
        Route::post('/', [FolderController::class, 'store']);
        Route::get('/{folder}', [FolderController::class, 'show']);
        Route::delete('/{folder}', [FolderController::class, 'destroy']);

        Route::get('/{id}/download', [FolderController::class, 'downloadZip']);
        Route::post('/generate-report', [FolderController::class, 'generateReport']);

        Route::post('/{id}/approve', [FolderController::class, 'approve']);
        Route::post('/{id}/reject', [FolderController::class, 'reject']);
    });

    Route::get('/designations', [DesignationController::class, 'index']);
    Route::prefix('/designation')->group(function () {
        Route::put('/{id}', [DesignationController::class, 'update']);
        Route::post('/', [DesignationController::class, 'store']);
        Route::get('/{designation}', [DesignationController::class, 'show']);
        Route::delete('/{designation}', [DesignationController::class, 'destroy']);
    });

    Route::prefix('/position')->group(function () {
        Route::post('/', [PositionController::class, 'store']);
        Route::put('/{id}', [PositionController::class, 'update']);
        Route::delete('/{id}', [PositionController::class, 'destroy']);
    });
});
