<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\RequestApprovalController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('admin')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('login', 'login');
            Route::post('register', 'register')->name('register');
            Route::post('logout', 'logout')
                ->middleware(['auth:admin', 'abilities:admin-access']);
        });
    });

    Route::middleware(['auth:admin', 'abilities:admin-access'])->group(function () {
        Route::controller(UserController::class)->prefix('user')->group(function () {
            Route::post('create', 'createUser');
            Route::patch('update', 'updateUser');
            Route::delete('delete', 'deleteUser');
        });
        Route::controller(RequestApprovalController::class)->prefix('requests')->group(function () {
            Route::get('', 'index');
            Route::get('pending', 'pendingRequest');
            Route::post('action', 'takeAction')
                ->middleware('admin.role:can_approve');
        });
    });
});
