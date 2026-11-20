<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Frontend\InquiryController;
use App\Http\Controllers\Frontend\InquiryInvoiceController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\SiteController;
use App\Http\Middleware\SetFrontendLocale;
use Illuminate\Support\Facades\Route;


// Route::get('/', [App\Http\Controllers\HomeController::class, 'home'])->name('home');
// ---------------------------------------------------------------
// Public website
// ---------------------------------------------------------------
Route::middleware(SetFrontendLocale::class)->group(function () {
    Route::get('/', [SiteController::class, 'home'])->name('home');
    Route::get('/contact', [SiteController::class, 'contact'])->name('contact');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}/{slug?}', [ProductController::class, 'show'])
        ->whereNumber('product')->name('products.show');
    // Customer invoice — short link sent by SMS (/i/{code})
    Route::get('/i/{code}', [InquiryInvoiceController::class, 'show'])
        ->where('code', '[A-Za-z0-9]{6,12}')->middleware('throttle:60,1')->name('inquiry.invoice');
    Route::post('/inquiry', [InquiryController::class, 'store'])
        ->middleware('throttle:5,1')->name('inquiry.store');
    Route::get('/lang/{locale}', [SiteController::class, 'switchLocale'])
        ->whereIn('locale', SetFrontendLocale::LOCALES)->name('lang.switch');
});
Route::get('/sitemap.xml', [SiteController::class, 'sitemap'])->name('sitemap');

// Admin login
Route::get('/login', [AdminController::class, 'adminLogin'])->name('login');

// Route::get('login', [App\Http\Controllers\HomeController::class, 'login'])->name('login');
// Route::post('validate/login', [App\Http\Controllers\HomeController::class, 'validateLogin'])->name('loginCheck');
// Route::get('/register', [App\Http\Controllers\HomeController::class, 'registration'])->name('registration');
// Route::post('store/register', [App\Http\Controllers\HomeController::class, 'storRegistration'])->name('storRegistration');
// Route::get('forget/password', [App\Http\Controllers\HomeController::class, 'loadForgetMyPass'])->name('forgetMyPass');
// Route::post('user/check', [App\Http\Controllers\HomeController::class, 'searchUser'])->name('searchUser');
// Route::match(['get', 'post'], 'user/load/otp', [HomeController::class, 'userOtpLoad'])->name('userOtpLoad');
// Route::post('validate/user/otp', [HomeController::class, 'validateUserOtp'])->name('validateUserOtp');
// Route::post('update/user/pass', [HomeController::class, 'updateUserPassword'])->name('updateUserPassword');

// // socialite

// Route::get('google/oauth/load', [App\Http\Controllers\HomeController::class, 'googleOauthLoad'])->name('googleOauthLoad');
// Route::get('google/auth/callback', [App\Http\Controllers\HomeController::class, 'googleOauthCallBack'])->name('googleOauthCallBack');




Route::prefix('user')->group(function () {

    Route::middleware(['auth'])->group(function () {

        Route::controller(HomeController::class)->group(function () {
            Route::get('dashboard', 'dashboard')->name('user.dashboard');
            Route::get('logout', 'logout')->name('user.logout');

        });

    });




});
