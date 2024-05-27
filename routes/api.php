<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\AuthencationController;
use App\Http\Controllers\API\EmailVerifiedlyController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\ChangePassword;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\API\NotificationController;


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
        Route::post('register', [AuthencationController::class, 'register'])->name('auth.register');
        Route::post('forgot-password', [PasswordResetController::class, 'sendEmail'])->name('auth.forgot-password');
        Route::post('reset-password', [ChangePassword::class, 'passwordResetProcess'])->name('auth.reset-password');
    });
    Route::prefix('notification')->group(function () {
        Route::post('send', [NotificationController::class, 'send'])->name('notification.send');
    });

    Route::prefix('email')->group(function () {
        Route::get('/verify/{id}/{hash}', [EmailVerifiedlyController::class, 'checkVerifyEmail'])->middleware(['auth', 'signed'])->name('verification.verify');
        Route::get('/verification-notification', [EmailVerifiedlyController::class, 'sendVerificationEmail'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');
    });

});

//=======================================USER======================================

Route::group(['prefix' => 'employee'], function () {
    Route::prefix('me')->group(function () {
        Route::post('', [EmployeeController::class, 'me'])->name('employee.me');
        Route::post('logout', [EmployeeController::class, 'logout'])->name('employee.logout');
        Route::post('login', [EmployeeController::class, 'login'])->name('employee.login');
        Route::post('change--info', [EmployeeController::class, 'changeInfo'])->name('employee.change-info');
    });
    Route::prefix('company')->group(function () {
        Route::post('detail-worker', [EmployeeController::class, 'detailEmployeeOthers'])->name('employee.detail-worker');
    });
    Route::prefix('check-in')->group(function () {
        Route::post('', [EmployeeController::class, 'checkIn'])->name('employee.check-in');
        Route::post('history-day', [EmployeeController::class, 'historyDaily'])->name('employee.historyDaily');
    });  
});
