<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DueDateAlertController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentCategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider within a group
| which is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 🔓 Public Routes
Route::post('signin', [AuthController::class, 'signIn'])->name('signin');
Route::post('payments/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

// 🔐 Protected Routes (require Sanctum token)
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::post('signout', [AuthController::class, 'signOut'])->name('signout');
    Route::get('self', [AuthController::class, 'self'])->name('self');

    // 👩‍👧 Parents Only (opsional — bisa dikosongkan jika semua route dipindah ke shared)
    Route::middleware(['role:parents'])->group(function () {
        Route::post('payments/{bill}', [PaymentController::class, 'store']);
        Route::post('payments/midtrans/{bill}', [PaymentController::class, 'createMidtransTransaction']);
        Route::get('notifications', [NotificationController::class, 'myNotifications']);
        Route::get('notifications/unread', [NotificationController::class, 'unread']);
        Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::get('due-date-alerts', [DueDateAlertController::class, 'index']);
        Route::get('mybills', [BillController::class, 'mybills']);
        // Tidak ada students di sini — dipindah ke shared
    });

    // 👨‍💼 + 👩‍👧 Shared routes (admin & parents) — DITEMPATKAN LEBIH AWAL
    Route::middleware(['role:admin,parents'])->group(function () {
        Route::get('bills', [BillController::class, 'index']);
        Route::get('bills/{bill}', [BillController::class, 'show']);
        Route::get('payment-history', [PaymentController::class, 'history']);   

        // ✅ Route khusus parents: pastikan didefinisikan DI SINI (sebelum apiResource students di admin)
        Route::get('students/my-students', [StudentController::class, 'myStudents']);
        Route::get('students/{id}', [StudentController::class, 'show'])
            ->where('id', '[0-9]+');
    });

    // 👨‍💼 Admin Only — DITEMPATKAN SETELAH SHARED
    Route::middleware(['role:admin'])->group(function () {
        Route::apiResource('academic-years', AcademicYearController::class);
        Route::apiResource('users', UserController::class);
        Route::apiResource('payment-categories', PaymentCategoryController::class);

        // ✅ apiResource('students') DI SINI — SETELAH route my-students
        Route::apiResource('students', StudentController::class);

        Route::apiResource('bills', BillController::class);
        Route::post('payments/{bill}', [PaymentController::class, 'store']);
        Route::post('payments/midtrans/{bill}', [PaymentController::class, 'createMidtransTransaction']);
        Route::post('bills/generate-monthly', [BillController::class, 'generateMonthlyBills']);
        Route::apiResource('due-date-alerts', DueDateAlertController::class);
    });
});
