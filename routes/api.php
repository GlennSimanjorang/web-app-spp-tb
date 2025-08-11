<?php
use Illuminate\Support\Facades\Route;

// ================================
// 1. ACADEMIC YEARS (Tahun Ajaran)
// ================================
Route::apiResource('academic-years', \App\Http\Controllers\AcademicYearController::class);


// ================================
// 2. PAYMENT CATEGORIES
// ================================
Route::apiResource('payment-categories', \App\Http\Controllers\PaymentCategoryController::class);


// ================================
// 3. USERS (Orang Tua)
// ================================
Route::apiResource('users', \App\Http\Controllers\UserController::class);

// ================================
// 4. STUDENTS (Siswa)
// ================================
Route::apiResource('students', \App\Http\Controllers\StudentController::class);
// ================================
// 5. BILLS (Tagihan)
// ================================
Route::apiResource('bills', \App\Http\Controllers\BillController::class);
// ================================
// 6. XENDIT: VIRTUAL ACCOUNT
// ================================
Route::prefix('xendit')->group(function () {
    // Buat VA untuk tagihan
    Route::post('/bills/{billId}/create-va', [\App\Http\Controllers\XenditVirtualAccountController::class, 'createVirtualAccount']);
    // Buat Invoice (Multi Payment)
    Route::post('/bills/{billId}/create-invoice', [\App\Http\Controllers\XenditInvoiceController::class, 'createInvoice']);
});
// POST /api/xendit/bills/{billId}/create-va
// POST /api/xendit/bills/{billId}/create-invoice


// ================================
// 7. XENDIT: VA & INVOICE (Detail)
// ================================
// GET /api/xendit/va/{id}
Route::get('/xendit/va/{id}', [\App\Http\Controllers\XenditVirtualAccountController::class, 'show']);
// GET /api/xendit/bills/{billId}/va
Route::get('/xendit/bills/{billId}/va', [\App\Http\Controllers\XenditVirtualAccountController::class, 'getByBill']);

// GET /api/xendit/invoice/{id}
Route::get('/xendit/invoice/{id}', [\App\Http\Controllers\XenditInvoiceController::class, 'show']);
// GET /api/xendit/bills/{billId}/invoice
Route::get('/xendit/bills/{billId}/invoice', [\App\Http\Controllers\XenditInvoiceController::class, 'getByBill']);


// ================================
// 8. PAYMENTS (Pembayaran)
// ================================
// GET /api/payments
Route::get('/payments', [\App\Http\Controllers\PaymentController::class, 'index']);
// GET /api/payments/{id}
Route::get('/payments/{id}', [\App\Http\Controllers\PaymentController::class, 'show']);
// GET /api/bills/{billId}/payments
Route::get('/bills/{billId}/payments', [\App\Http\Controllers\PaymentController::class, 'getByBill']);


// ================================
// 9. XENDIT CALLBACK (Webhook)
// ================================
// POST /api/xendit/callback ← Dari Xendit
Route::post('/xendit/callback', [\App\Http\Controllers\XenditCallbackController::class, 'handle']);
// GET /api/xendit/callbacks ← Untuk monitoring
Route::get('/xendit/callbacks', [\App\Http\Controllers\XenditCallbackController::class, 'index']);


// ================================
// 10. NOTIFICATIONS (Notifikasi)
// ================================
// GET /api/notifications
Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
// GET /api/users/{userId}/notifications
Route::get('/users/{userId}/notifications', [\App\Http\Controllers\NotificationController::class, 'getByUser']);
// GET /api/users/{userId}/notifications/unread
Route::get('/users/{userId}/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'getUnread']);
// PUT /api/notifications/{id}/read
Route::put('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
// PUT /api/users/{userId}/notifications/read-all
Route::put('/users/{userId}/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
// POST /api/notifications
Route::post('/notifications', [\App\Http\Controllers\NotificationController::class, 'create']);
// DELETE /api/notifications/{id}
Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy']);


// ================================
// 11. PAYMENT REPORTS (Laporan)
// ================================
// GET /api/reports
Route::get('/reports', [\App\Http\Controllers\PaymentReportController::class, 'index']);
// GET /api/reports/{id}
Route::get('/reports/{id}', [\App\Http\Controllers\PaymentReportController::class, 'show']);
// POST /api/reports/daily
Route::post('/reports/daily', [\App\Http\Controllers\PaymentReportController::class, 'generateDailyReport']);
// POST /api/reports/monthly
Route::post('/reports/monthly', [\App\Http\Controllers\PaymentReportController::class, 'generateMonthlyReport']);


// ================================
// 12. DUE DATE ALERTS
// ================================
// GET /api/alerts
Route::get('/alerts', [\App\Http\Controllers\DueDateAlertController::class, 'index']);
// PUT /api/alerts/{id}/processed
Route::put('/alerts/{id}/processed', [\App\Http\Controllers\DueDateAlertController::class, 'markAsProcessed']);