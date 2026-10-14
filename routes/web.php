<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ListingController as AdminListingController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ListingPerformanceController;
use App\Http\Controllers\Frontend\RoomController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\ListingController as OwnerListingController;
use App\Http\Controllers\Owner\PaymentController as OwnerPaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/rooms', fn (Request $request) => redirect()->route('home', $request->query()))
    ->name('rooms.index');
Route::get('/rooms/{room}', fn () => redirect()->route('home'))
    ->whereNumber('room');
Route::get('/listing/{room}', [RoomController::class, 'show'])->name('listings.show');
Route::post('/listing/{room}/performance', [ListingPerformanceController::class, 'record'])
    ->middleware('throttle:60,1')
    ->name('listings.performance.record');
Route::post('/listing/{room}/like', [ListingPerformanceController::class, 'toggleLike'])
    ->middleware('throttle:30,1')
    ->name('listings.performance.like');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', fn () => redirect()->route('home'))->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:owner,admin'])->group(function () {
    Route::redirect('/owner', '/owner/dashboard')->name('owner.root');
    Route::get('/owner/dashboard', [OwnerDashboardController::class, 'index'])
        ->name('owner.dashboard');

    Route::get('/owner/listings', [OwnerListingController::class, 'index'])
        ->name('owner.listings.index');
    Route::get('/owner/listings/create', [OwnerListingController::class, 'create'])
        ->name('owner.listings.create');
    Route::post('/owner/listings', [OwnerListingController::class, 'store'])
        ->name('owner.listings.store');
    Route::post('/owner/listings/{room}/submit', [OwnerListingController::class, 'submitForApproval'])
        ->name('owner.listings.submit');
    Route::get('/owner/listings/{room}', [OwnerListingController::class, 'show'])
        ->name('owner.listings.show');
    Route::get('/owner/listings/{room}/edit', [OwnerListingController::class, 'edit'])
        ->name('owner.listings.edit');
    Route::put('/owner/listings/{room}', [OwnerListingController::class, 'update'])
        ->name('owner.listings.update');
    Route::delete('/owner/listings/{room}', [OwnerListingController::class, 'destroy'])
        ->name('owner.listings.destroy');

    Route::get('/owner/payments', [OwnerPaymentController::class, 'index'])
        ->name('owner.payments.index');
    Route::post('/owner/payments/{listingFee}/pay', [OwnerPaymentController::class, 'pay'])
        ->name('owner.payments.pay');
});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::redirect('/admin', '/admin/dashboard')->name('admin.root');
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
    Route::get('/admin/listings', [AdminListingController::class, 'index'])
        ->name('admin.listings.index');
    Route::get('/admin/users', [AdminUserController::class, 'index'])
        ->name('admin.users.index');
    Route::get('/admin/users/create', [AdminUserController::class, 'create'])
        ->name('admin.users.create');
    Route::post('/admin/users', [AdminUserController::class, 'store'])
        ->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])
        ->name('admin.users.edit');
    Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])
        ->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])
        ->name('admin.users.destroy');
    Route::get('/admin/payments', [AdminPaymentController::class, 'index'])
        ->name('admin.payments.index');
    Route::get('/admin/reports', [AdminReportController::class, 'index'])
        ->name('admin.reports.index');
    Route::post('/admin/listings/{room}/approve', [AdminListingController::class, 'approve'])
        ->name('admin.listings.approve');
    Route::post('/admin/listings/{room}/reject', [AdminListingController::class, 'reject'])
        ->name('admin.listings.reject');
    Route::delete('/admin/listings/{room}', [AdminListingController::class, 'destroy'])
        ->name('admin.listings.destroy');
});

require __DIR__.'/auth.php';
