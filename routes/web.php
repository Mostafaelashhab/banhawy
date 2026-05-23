<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SignupController;
use App\Http\Controllers\Public\AccountController;
use App\Http\Controllers\Public\FavoritesController;
use App\Http\Controllers\Merchant\AnalyticsController;
use App\Http\Controllers\Merchant\BookingController as MerchantBookingController;
use App\Http\Controllers\Merchant\DashboardController;
use App\Http\Controllers\Merchant\OrderController as MerchantOrderController;
use App\Http\Controllers\Merchant\PhotosController as MerchantPhotosController;
use App\Http\Controllers\Merchant\QrController;
use App\Http\Controllers\Merchant\SettingsController;
use App\Http\Controllers\Public\BusinessController;
use App\Http\Controllers\Public\ClaimController;
use App\Http\Controllers\Public\DiscoverController;
use App\Http\Controllers\Public\MapController;
use App\Http\Controllers\Public\BookingController as PublicBookingController;
use App\Http\Controllers\Public\OrderController as PublicOrderController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\SplashController;
use App\Http\Controllers\Public\TrackController;
use Illuminate\Support\Facades\Route;

// ── Public ─────────────────────────────────────────────────────
Route::get('/',         SplashController::class)->name('landing');
Route::get('/discover', DiscoverController::class)->name('home');
Route::view('/offline',  'public.offline')->name('offline');
Route::get('/track',    TrackController::class)->name('track');
Route::get('/map',      MapController::class)->name('map');
Route::get('/search',   SearchController::class)->name('search');

Route::prefix('biz')->group(function () {
    Route::get('/{business:slug}',          [BusinessController::class, 'show'])->name('business.show');
    Route::get('/{business:slug}/menu',     [BusinessController::class, 'menu'])->name('business.menu');
    Route::get('/{business:slug}/whatsapp', [BusinessController::class, 'whatsapp'])->name('business.whatsapp');

    Route::get('/{business:slug}/order',          [PublicOrderController::class, 'summary'])->name('business.order.summary');
    Route::post('/{business:slug}/order',         [PublicOrderController::class, 'place'])->name('business.order.place');
    Route::get('/{business:slug}/order/{order}/success', [PublicOrderController::class, 'success'])->name('business.order.success');

    Route::get('/{business:slug}/book',           [PublicBookingController::class, 'form'])->name('business.book.form');
    Route::post('/{business:slug}/book',          [PublicBookingController::class, 'store'])->name('business.book.store');
    Route::get('/{business:slug}/book/{booking}/success', [PublicBookingController::class, 'success'])->name('business.book.success');

    Route::post('/{business:slug}/claim', [ClaimController::class, 'store'])
        ->middleware('throttle:6,60')
        ->name('business.claim');
});

// ── Guest auth ─────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',   [LoginController::class, 'show'])->name('login');
    Route::post('/login',  [LoginController::class, 'store'])->name('login.attempt');

    // Visitor signup (simple)
    Route::get('/signup',  [SignupController::class, 'show'])->name('signup');
    Route::post('/signup', [SignupController::class, 'store'])->name('signup.attempt');
});

// Merchant register step 1 — works for guests AND already-logged-in visitors
// (auth visitors get skipped to step 2 by the controller)
Route::get('/register',  [RegisterController::class, 'step1'])->name('register.step1');
Route::post('/register', [RegisterController::class, 'step1Store'])->name('register.step1.store');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Favorites (any logged-in user)
    Route::get('/favorites',                  [FavoritesController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{business:slug}', [FavoritesController::class, 'toggle'])->name('favorites.toggle');

    // Visitor account page
    Route::get('/account', [AccountController::class, 'index'])->name('account');

    // Web-push endpoints
    Route::post('/push/subscribe',   [\App\Http\Controllers\PushController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [\App\Http\Controllers\PushController::class, 'unsubscribe'])->name('push.unsubscribe');
    Route::post('/push/test',        [\App\Http\Controllers\PushController::class, 'test'])->name('push.test');

    Route::get('/register/type',     [RegisterController::class, 'step2'])->name('register.step2');
    Route::post('/register/type',    [RegisterController::class, 'step2Store'])->name('register.step2.store');
    Route::get('/register/details',  [RegisterController::class, 'step3'])->name('register.step3');
    Route::post('/register/details', [RegisterController::class, 'step3Store'])->name('register.step3.store');
    Route::get('/register/plan',     [RegisterController::class, 'step4'])->name('register.step4');
    Route::post('/register/plan',    [RegisterController::class, 'step4Store'])->name('register.step4.store');
    Route::get('/register/{business:slug}/success', [RegisterController::class, 'success'])->name('register.success');

    Route::prefix('m')->name('merchant.')->group(function () {
        Route::get('/dashboard',           DashboardController::class)->name('dashboard');
        Route::get('/orders',              [MerchantOrderController::class, 'index'])->name('orders.index');
        Route::patch('/orders/{order}',    [MerchantOrderController::class, 'update'])->name('orders.update');
        Route::get('/bookings',            [MerchantBookingController::class, 'index'])->name('bookings.index');
        Route::patch('/bookings/{booking}',[MerchantBookingController::class, 'update'])->name('bookings.update');
        Route::get('/analytics',           AnalyticsController::class)->name('analytics');
        Route::get('/qr',                  QrController::class)->name('qr');
        Route::get('/settings',            [SettingsController::class, 'index'])->name('settings');
        Route::patch('/settings',          [SettingsController::class, 'update'])->name('settings.update');

        Route::get('/photos',              [MerchantPhotosController::class, 'index'])->name('photos');
        Route::post('/photos',             [MerchantPhotosController::class, 'store'])->name('photos.store');
        Route::delete('/photos',           [MerchantPhotosController::class, 'destroy'])->name('photos.destroy');
        Route::post('/photos/cover',       [MerchantPhotosController::class, 'setCover'])->name('photos.cover');
        Route::post('/photos/logo',        [MerchantPhotosController::class, 'setLogo'])->name('photos.logo');
    });
});
