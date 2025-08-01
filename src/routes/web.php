<?php

use Illuminate\Support\Facades\Route;

use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;

use App\Http\Controllers\Staff\RegisterController;
use App\Http\Controllers\Staff\EmailVerificationPromptController;
use App\Http\Controllers\Staff\RequestController;
use App\Http\Controllers\Staff\AttendanceController as UserAttendanceController;
use App\Http\Controllers\Staff\AuthenticationController as UserAuthController;

use App\Http\Controllers\Admin\AuthenticationController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StaffAttendanceController;
use App\Http\Controllers\Admin\AttendanceApprovalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register.form');
    Route::post('/register', [RegisterController::class, 'store'])->name('register');

    Route::get('/login', [UserAuthController::class, 'create'])->name('login.form');
    Route::post('/login', [UserAuthController::class, 'store'])->name('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::middleware('auth', 'verified')->group(function () {

    Route::post('/logout', [UserAuthController::class, 'destroy'])->name('logout');

    Route::get('/attendance', [UserAttendanceController::class, 'create'])->name('staff.attendance.create');
    Route::post('/attendance', [UserAttendanceController::class, 'handleAction'])->name('staff.attendance.action');

    Route::get('/attendance/list', [UserAttendanceController::class, 'list'])->name('staff.attendance.list');

    Route::get('/attendance/{id}', [UserAttendanceController::class, 'show'])->name('staff.attendance.show');
    Route::post('/attendance/{id}', [RequestController::class, 'requestUpdate'])->name('staff.attendance.request_update');

    Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])->name('staff.request.list');

    Route::get('/stamp_correction_request/{id}', [RequestController::class, 'show'])->name('staff.request.show');
});


Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login.form');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.submit');
    });

    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

    // auth に変更し、role=adminをミドルウェアでチェック
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendance.list');

        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
        Route::post('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('attendance.update');

        Route::get('/staff/list', [StaffController::class, 'index'])->name('staff.list');
        Route::get('/attendance/staff/{id}', [StaffAttendanceController::class, 'show'])->name('staff.attendance');
        Route::get('/staff/{id}/attendances/csv', [StaffAttendanceController::class, 'exportCsv'])
            ->name('staff.attendance.csv');

        Route::get('/stamp_correction_request/list', [AttendanceApprovalController::class, 'index'])->name('request.list');

        Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AttendanceApprovalController::class, 'show'])->name('request.approve.show');
        Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AttendanceApprovalController::class, 'approve'])->name('request.approve.submit');
    });
});
