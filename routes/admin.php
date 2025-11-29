<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('admin')->group(function () {

     Route::controller(AdminController::class)->group(function () {
                Route::get('login', 'adminLogin')->name('adminLogin');
                Route::get('load/forgetpass', 'loadForgetMyPass')->name('loadForgetMyPass');
                Route::post('find/user', 'findUser')->name('findUser');
                Route::post('validate/login', 'adminValidateLogin')->name('adminValidateLogin');
                Route::post('update/password', 'updatePassword')->name('updatePassword');
                Route::post('validate/otp', 'validateOtp')->name('validateOtp');


        //     Route::post('update/{survey:uuid}', 'update');
        //     Route::get('show/{survey:uuid}', 'show');
        //     Route::get('cheak/status/{survey:uuid}', 'cheakStatus');
         });
         Route::match(['get', 'post'], 'load/otp', [AdminController::class, 'otpLoad'])->name('otpLoad');

    Route::middleware(['auth:admin'])->group(function () {

        Route::controller(AdminController::class)->group(function () {
            Route::get('dashboard', 'dashboard')->name('admin.dashboard');
            Route::get('logout', 'logout')->name('admin.logout');

        });

        // Admin User Management Routes
        Route::controller(AdminUserController::class)->group(function () {
            Route::get('user/index', 'index')->name('admin.adminUserIndex');
            Route::get('user/create', 'create')->name('admin.adminUserCreate');
            Route::post('user/store', 'store')->name('admin.adminUserStore');
            Route::get('user/{admin}/edit', 'edit')->name('admin.adminUserEdit');
            Route::put('user/{admin}/update', 'update')->name('admin.adminUserUpdate');
            Route::get('user/{admin}', 'show')->name('admin.adminUserShow');
            Route::delete('user/{admin}/delete', 'destroy')->name('admin.adminUserDestroy');
            Route::put('user/{admin}/toggle-status', 'toggleStatus')->name('admin.adminUserToggleStatus');
        });

    });

});
