<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\AuthencationController;
use App\Http\Controllers\API\EmailController;


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


// ========================================ADMIN========================================

Route::group(['middleware' => 'api','prefix' => 'admin'], function () {
    Route::prefix('profile')->group(function () {
        Route::get('', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('add', [ProfileController::class, 'store'])->name('profile.add');
        Route::get('show/{id}', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('update/{id}', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('delete/{id}', [ProfileController::class, 'destroy'])->name('profile.delete');
    });
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthencationController::class, 'login'])->name('auth.login');
        Route::post('logout', [AuthencationController::class, 'logout'])->name('auth.logout');
        Route::post('refresh', [AuthencationController::class, 'refresh'])->name('auth.refresh');
        Route::post('me', [AuthencationController::class, 'me'])->name('auth.me');
        Route::post('u', [AuthencationController::class, 'u'])->name('auth.register');
        Route::post('forgot-password', [AuthencationController::class, 'forgotPassword'])->name('auth.forgot-password');
        Route::post('reset-password', [AuthencationController::class, 'resetPassword'])->name('auth.reset-password');
        Route::post('okk', [AuthencationController::class, 'okk']);
    });
});

Route::prefix('email')->group(function () {
    Route::get('/verify/{id}/{hash}', [EmailController::class, 'checkVerifyEmail'])->middleware(['auth', 'signed'])->name('verification.verify');
    Route::get('/verification-notification', [EmailController::class, 'sendVerificationEmail'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');
});


//=======================================USER========================================

