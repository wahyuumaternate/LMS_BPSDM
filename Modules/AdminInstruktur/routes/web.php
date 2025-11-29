<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Modules\AdminInstruktur\Http\Controllers\AdminInstrukturController;
use Modules\AdminInstruktur\Http\Controllers\AuthController;
use Modules\AdminInstruktur\Http\Controllers\DashboardController;
use Modules\AdminInstruktur\Http\Controllers\LogoutController;
use Modules\AdminInstruktur\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes for Admin and Instructor Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login-admin', function () {
    return redirect('/admin/login');
})->name('login');

// Authentication Routes
Route::group(['prefix' => 'admin'], function () {
    // Guest routes
    Route::middleware('guest:admin_instruktur')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
    });

    // Protected routes - Use the standard auth middleware with the admin_instruktur guard
    Route::middleware('auth:admin_instruktur')->group(function () {
        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

        // Profile
        Route::get('/profile', [AuthController::class, 'profile'])->name('admin.profile');
        Route::get('/profile/edit', [AuthController::class, 'editProfile'])->name('admin.profile.edit');
        Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('admin.profile.update');

        // Password
        Route::get('/password/change', [AuthController::class, 'showChangePasswordForm'])->name('admin.password.change');
        Route::post('/password/update', [AuthController::class, 'changePassword'])->name('admin.password.update');

         // Dashboard - Role-based
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Dashboard AJAX endpoints (optional - for dynamic updates)
        Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
        
        // // Admin & Instruktur Management (Super Admin only)
        // Route::middleware(['role:super_admin'])->group(function () {
        // });
        Route::resource('admin', AdminInstrukturController::class);
    








        // Admin-only routes
        Route::middleware(['admin'])->prefix('admin')->group(function () {
            // Add admin-specific routes here

            // Example:
            // Route::resource('users', AdminUserController::class);
        });

        // Instructor-only routes
        Route::middleware(['instruktur'])->prefix('instruktur')->group(function () {
            // Add instructor-specific routes here

            // Example:
            // Route::resource('courses', InstructorCourseController::class);
        });

        Route::resource('manajemen-admin', AdminInstrukturController::class)->names([
        'index' => 'admin.index',
        'create' => 'admin.create',
        'store' => 'admin.store',
        'show' => 'admin.show',
        'edit' => 'admin.edit',
        'update' => 'admin.update',
        'destroy' => 'admin.destroy',
    ]);
    });

      Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });
});


// Redirect root to admin login for convenience
Route::redirect('/', '/admin/dashboard');
