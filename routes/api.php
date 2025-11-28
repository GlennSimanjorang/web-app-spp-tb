<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DueDateAlertController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentCategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;

// 🔓 Public Route: Login
Route::post('signin', [AuthController::class, 'signIn'])->name('signin');
Route::post('payments/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

// 🔐 Protected Routes: Harus login
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth: Logout & Profile
    Route::post('signout', [AuthController::class, 'signOut'])->name('signout');
    Route::get('self', [AuthController::class, 'self'])->name('self');

    // 👨‍💼 Rute untuk ADMIN SAJA
    Route::middleware(['role:admin'])->group(function () {
        // Academic Years
        Route::apiResource('academic-years', AcademicYearController::class);

        Route::apiResource('users', UserController::class);

        // Payment Categories
        Route::apiResource('payment-categories', PaymentCategoryController::class);

        // Students
        Route::apiResource('students', StudentController::class);

        Route::apiResource('bills', BillController::class);

        // Payments (Admin bisa buat manual atau proses Midtrans)
        Route::post('payments/{bill}', [PaymentController::class, 'store']);
        Route::post('payments/midtrans/{bill}', [PaymentController::class, 'createMidtransTransaction']);

        // Payment Reports
        Route::post('bills/generate-monthly', [BillController::class, 'generateMonthlyBills']);

        // Due Date Alerts (Admin lihat semua)
        Route::apiResource('due-date-alerts', DueDateAlertController::class);

        // Notifications (Admin bisa buat/broadcast)
        Route::post('notifications', [NotificationController::class, 'store']);
        Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);
    });

    // 👩‍👧 Rute untuk PARENTS
    Route::middleware(['role:parents'])->group(function () {

        // Bayar tagihan
        Route::post('payments/{bill}', [PaymentController::class, 'store']);
        Route::post('payments/midtrans/{bill}', [PaymentController::class, 'createMidtransTransaction']);

        // Notifications: Hanya milik sendiri
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']); // Hanya milik user

        Route::get('due-date-alerts', [DueDateAlertController::class, 'index']);

        

        Route::get('students/my-students', [StudentController::class, 'myStudents']);
        Route::get('students/{id}', [StudentController::class, 'show'])
            ->where('id', '[0-9]+'); // hanya ID valid
    });

    // Satu route untuk semua role yang diizinkan
    Route::middleware(['role:admin,parents'])->group(function () {
        Route::get('bills', [BillController::class, 'index']);
        Route::get('bills/{bill}', [BillController::class, 'show']);
        Route::get('/payment-history', [PaymentController::class, 'history']);
        
    });
});


