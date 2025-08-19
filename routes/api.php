<?php

use Illuminate\Support\Facades\Route;

// ================================
// 🔐 AUTH ROUTES (Public)
// ================================
Route::post('signin', [\App\Http\Controllers\AuthController::class, 'signIn'])->name('signin');

// ================================
// 🔑 PROTECTED ROUTES (Butuh Token)
// ================================
Route::middleware(['token-check'])->group(function () {
    // 🔽 Logout & Profil (Semua role)
    Route::post('signout', [\App\Http\Controllers\AuthController::class, 'signOut'])->name('signout');
    Route::get('self', [\App\Http\Controllers\AuthController::class, 'self'])->name('self');

    // ================================
    // 👨‍💼 ADMIN ROUTES (Hanya Admin)
    // ================================
    Route::middleware(['role:admin'])->group(function () {
        // CRUD Master Data
        Route::apiResource('academic-years', \App\Http\Controllers\AcademicYearController::class);
        Route::apiResource('payment-categories', \App\Http\Controllers\PaymentCategoryController::class);
        Route::apiResource('users', \App\Http\Controllers\UserController::class);
        Route::apiResource('students', \App\Http\Controllers\StudentController::class);
        Route::apiResource('bills', \App\Http\Controllers\BillController::class);

        // Xendit: Buat Invoice (Admin bisa buat untuk orang tua)
        Route::post('/bills/{billId}/invoice', [\App\Http\Controllers\XenditInvoiceController::class, 'storeByBill']);

        // Laporan
        Route::get('/reports', [\App\Http\Controllers\PaymentReportController::class, 'index']);
        Route::get('/reports/{id}', [\App\Http\Controllers\PaymentReportController::class, 'show']);
        Route::post('/reports/daily', [\App\Http\Controllers\PaymentReportController::class, 'generateDailyReport']);
        Route::post('/reports/monthly', [\App\Http\Controllers\PaymentReportController::class, 'generateMonthlyReport']);

        // Monitoring
        Route::get('/xendit/callbacks', [\App\Http\Controllers\XenditCallbackController::class, 'index']);
        Route::get('/payments', [\App\Http\Controllers\PaymentController::class, 'index']);
        Route::get('/payments/{id}', [\App\Http\Controllers\PaymentController::class, 'show']);
        Route::get('/bills/{billId}/payments', [\App\Http\Controllers\PaymentController::class, 'getByBill']);

        // Alerts
        Route::get('/alerts', [\App\Http\Controllers\DueDateAlertController::class, 'index']);
        Route::put('/alerts/{id}/processed', [\App\Http\Controllers\DueDateAlertController::class, 'markAsProcessed']);
    });

    // ================================
    // 👨‍👩‍👧 PARENT ROUTES (Orang Tua)
    // ================================
    Route::middleware(['role:parent'])->group(function () {
        // Siswa yang dimiliki user (hanya miliknya sendiri)
        Route::get('/students/my', [\App\Http\Controllers\StudentController::class, 'myStudents']);

        // Tagihan untuk anaknya
        Route::get('/bills/my', [\App\Http\Controllers\BillController::class, 'myBills']);

        // Dapatkan invoice untuk tagihan anak
        Route::get('/bills/{billId}/invoice', [\App\Http\Controllers\XenditInvoiceController::class, 'getByBill']);

        // Cek status invoice
        Route::get('/xendit/invoices/{invoiceId}', [\App\Http\Controllers\XenditInvoiceController::class, 'checkStatus']);
        Route::get('/xendit/invoices/{invoiceId}/status', [\App\Http\Controllers\XenditInvoiceController::class, 'checkStatus']);

        // Pembayaran anaknya
        Route::get('/bills/{billId}/payments', [\App\Http\Controllers\PaymentController::class, 'getByBill']);

        // Notifikasi
        Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'getByUser']);
        Route::get('/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'getUnread']);
        Route::put('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
        Route::put('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    });

    // ================================
    // 🌐 XENDIT CALLBACK (Webhook - Tidak butuh token, tapi bisa validasi signature)
    // ================================
    // Webhook dari Xendit (bisa diakses publik, tapi kita validasi di controller)
    Route::post('/xendit/callback', [\App\Http\Controllers\XenditCallbackController::class, 'handle']);
});
