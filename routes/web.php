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
use App\Http\Controllers\Public\LostItemController;
use App\Http\Controllers\Public\ReportController;
use App\Http\Controllers\Public\RoadAlertController;
use App\Http\Controllers\Public\TaskController;
use App\Http\Controllers\Public\DiscoverController;
use App\Http\Controllers\Public\MapController;
use App\Http\Controllers\Public\BookingController as PublicBookingController;
use App\Http\Controllers\Public\OrderController as PublicOrderController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\SplashController;
use App\Http\Controllers\Public\TrackController;
use Illuminate\Support\Facades\Route;

// PWA manifest served dynamically so icon URLs always carry the current asset version
Route::get('/manifest.json', function () {
    $ver = @file_get_contents(public_path('icons/.version')) ?: '1';
    $raw = (string) @file_get_contents(resource_path('manifest.json'));
    $data = json_decode($raw, true) ?: [];

    $bump = function (array $icons) use ($ver) {
        return array_map(function ($icon) use ($ver) {
            if (isset($icon['src']) && ! str_contains($icon['src'], '?v=')) {
                $icon['src'] .= '?v=' . $ver;
            }
            return $icon;
        }, $icons);
    };
    if (isset($data['icons'])) $data['icons'] = $bump($data['icons']);
    if (isset($data['shortcuts'])) {
        foreach ($data['shortcuts'] as &$s) {
            if (isset($s['icons'])) $s['icons'] = $bump($s['icons']);
        }
    }

    return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        ->header('Cache-Control', 'no-cache, must-revalidate')
        ->header('Content-Type', 'application/manifest+json; charset=utf-8');
});

// ── Public ─────────────────────────────────────────────────────
Route::get('/',         SplashController::class)->name('landing');
Route::get('/discover', DiscoverController::class)->name('home');
Route::view('/offline',  'public.offline')->name('offline');
Route::get('/track',    TrackController::class)->name('track');
Route::get('/map',      MapController::class)->name('map');

// ── Road & safety alerts (أمان وطريق بنها) ──────────────────────
Route::get('/alerts/active',              [RoadAlertController::class, 'active'])->name('alerts.active');
Route::post('/alerts',                    [RoadAlertController::class, 'store'])->middleware('throttle:20,60')->name('alerts.store');
Route::post('/alerts/{alert}/confirm',    [RoadAlertController::class, 'confirm'])->middleware('throttle:60,60')->name('alerts.confirm');
Route::post('/alerts/{alert}/reject',     [RoadAlertController::class, 'reject'])->middleware('throttle:60,60')->name('alerts.reject');
Route::get('/search',   SearchController::class)->name('search');

// Dedicated category landing pages
Route::get('/shipping', [\App\Http\Controllers\Public\CategoryListingController::class, 'shipping'])->name('shipping');
Route::get('/services', [\App\Http\Controllers\Public\CategoryListingController::class, 'services'])->name('services');

// Pricing / monetization page (services-focused subscription model)
Route::get('/pricing', function () {
    $plans = \App\Models\Plan::orderBy('sort')->get();
    return view('public.pricing', compact('plans'));
})->name('pricing');

// ── Tasks (مهام) ─────────────────────────────────────────────────
Route::get('/tasks',           [TaskController::class, 'index'])->name('tasks.index');
Route::get('/tasks/new',       [TaskController::class, 'create'])->name('tasks.create');
Route::post('/tasks',          [TaskController::class, 'store'])->middleware('throttle:5,60')->name('tasks.store');
Route::get('/tasks/{task}',    [TaskController::class, 'show'])->name('tasks.show');
Route::patch('/tasks/{task}/close', [TaskController::class, 'close'])->middleware('auth')->name('tasks.close');

// ── Lost & Found (مفقودات) ──────────────────────────────────────
Route::get('/lost',             [LostItemController::class, 'index'])->name('lost.index');
Route::get('/lost/new',         [LostItemController::class, 'create'])->name('lost.create');
Route::post('/lost',            [LostItemController::class, 'store'])->middleware('throttle:5,60')->name('lost.store');
Route::get('/lost/{lost}',      [LostItemController::class, 'show'])->name('lost.show');
Route::patch('/lost/{lost}/resolve', [LostItemController::class, 'resolve'])->middleware('auth')->name('lost.resolve');

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

    Route::post('/{business:slug}/report', [ReportController::class, 'store'])
        ->middleware('throttle:8,60')
        ->name('business.report');
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

    // ── Admin panel ─────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', \App\Http\Controllers\Admin\DashboardController::class)->name('dashboard');

        // Businesses
        Route::get('/businesses',                [\App\Http\Controllers\Admin\BusinessController::class, 'index'])->name('businesses.index');
        Route::get('/businesses/{business}/edit',[\App\Http\Controllers\Admin\BusinessController::class, 'edit'])->name('businesses.edit');
        Route::patch('/businesses/{business}',   [\App\Http\Controllers\Admin\BusinessController::class, 'update'])->name('businesses.update');
        Route::post('/businesses/{business}/toggle',  [\App\Http\Controllers\Admin\BusinessController::class, 'toggle'])->name('businesses.toggle');
        Route::post('/businesses/{business}/invite',  [\App\Http\Controllers\Admin\BusinessController::class, 'invite'])->name('businesses.invite');
        Route::delete('/businesses/{business}',  [\App\Http\Controllers\Admin\BusinessController::class, 'destroy'])->name('businesses.destroy');

        // Claims
        Route::get('/claims',                       [\App\Http\Controllers\Admin\ClaimController::class, 'index'])->name('claims.index');
        Route::get('/claims/{claim}',               [\App\Http\Controllers\Admin\ClaimController::class, 'show'])->name('claims.show');
        Route::post('/claims/{claim}/approve',      [\App\Http\Controllers\Admin\ClaimController::class, 'approve'])->name('claims.approve');
        Route::post('/claims/{claim}/reject',       [\App\Http\Controllers\Admin\ClaimController::class, 'reject'])->name('claims.reject');

        // Reports
        Route::get('/reports',                      [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{report}',             [\App\Http\Controllers\Admin\ReportController::class, 'show'])->name('reports.show');
        Route::patch('/reports/{report}',           [\App\Http\Controllers\Admin\ReportController::class, 'update'])->name('reports.update');

        // Users
        Route::get('/users',                        [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role',          [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{user}',              [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

        // Reviews
        Route::get('/reviews',                      [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::delete('/reviews/{review}',          [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

        // Tasks (مهام)
        Route::get('/tasks',           [\App\Http\Controllers\Admin\TaskController::class, 'index'])->name('tasks.index');
        Route::patch('/tasks/{task}',  [\App\Http\Controllers\Admin\TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [\App\Http\Controllers\Admin\TaskController::class, 'destroy'])->name('tasks.destroy');

        // Lost & found (مفقودات)
        Route::get('/lost',          [\App\Http\Controllers\Admin\LostItemController::class, 'index'])->name('lost.index');
        Route::patch('/lost/{lost}', [\App\Http\Controllers\Admin\LostItemController::class, 'update'])->name('lost.update');
        Route::delete('/lost/{lost}',[\App\Http\Controllers\Admin\LostItemController::class, 'destroy'])->name('lost.destroy');

        // Road & safety alerts (أمان وطريق بنها)
        Route::get('/alerts',                 [\App\Http\Controllers\Admin\RoadAlertController::class, 'index'])->name('alerts.index');
        Route::patch('/alerts/{alert}',       [\App\Http\Controllers\Admin\RoadAlertController::class, 'update'])->name('alerts.update');
        Route::delete('/alerts/{alert}',      [\App\Http\Controllers\Admin\RoadAlertController::class, 'destroy'])->name('alerts.destroy');
    });
});
