<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AttendanceController as UserAttendanceController;
use App\Http\Controllers\AuthenticatedSessionController as UserAuthController;

use App\Http\Controllers\Admin\AuthenticatedSessionController as AdminAuthController;
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

Route::middleware('guest')->group(function () {

    Route::get('/register', [RegisterController::class, 'index'])->name('register.form');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.submit');

    Route::get('/login', [UserAuthController::class, 'index'])->name('login.form');
    Route::post('/login', [UserAuthController::class, 'store'])->name('login.submit');
});


Route::middleware('auth')->group(function () {

    Route::get('/attendance', [UserAttendanceController::class, 'index'])->name('attendance.form');
    Route::post('/attendance', [UserAttendanceController::class, 'handleAction'])->name('attendance.action');

    Route::get('/attendance/list', [UserAttendanceController::class, 'list'])->name('attendance.list');

    Route::get('/attendance/{id}', [UserAttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/{id}', [UserAttendanceController::class, 'requestUpdate'])->name('attendance.request_update');

    Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])->name('request.list');
});


Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {

        Route::get('/login', [AdminAuthController::class, 'index'])->name('login.form');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.submit');
    });

    Route::middleware('auth:admin')->group(function () {

        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendance.list');

        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
        Route::post('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('attendance.update');

        Route::get('/staff/list', [StaffController::class, 'index'])->name('staff.list');
        Route::get('/attendance/staff/{id}', [StaffAttendanceController::class, 'show'])->name('staff.attendance');

        Route::get('/stamp_correction_request/list', [StaffAttendanceController::class, 'index'])->name('request.list');

        Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AttendanceApprovalController::class, 'show'])->name('request.approve.show');
        Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AttendanceApprovalController::class, 'approve'])->name('request.approve.submit');
    });
});
