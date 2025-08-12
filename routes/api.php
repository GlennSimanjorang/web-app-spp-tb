<?php
use Illuminate\Support\Facades\Route;


Route::apiResource('academic-years', \App\Http\Controllers\AcademicYearController::class);
Route::apiResource('payment-categories', \App\Http\Controllers\PaymentCategoryController::class);
Route::apiResource('users', \App\Http\Controllers\UserController::class);
Route::apiResource('students', \App\Http\Controllers\StudentController::class);
Route::apiResource('bills', \App\Http\Controllers\BillController::class);
Route::prefix('xendit')->group(function () {
    Route::post('/bills/{billId}/create-va', [\App\Http\Controllers\XenditVirtualAccountController::class, 'createVirtualAccount']);
    Route::post('/bills/{billId}/create-invoice', [\App\Http\Controllers\XenditInvoiceController::class, 'createInvoice']);
});

Route::get('/xendit/va/{id}', [\App\Http\Controllers\XenditVirtualAccountController::class, 'show']);
Route::get('/xendit/bills/{billId}/va', [\App\Http\Controllers\XenditVirtualAccountController::class, 'getByBill']);
Route::get('/xendit/invoice/{id}', [\App\Http\Controllers\XenditInvoiceController::class, 'show']);
Route::get('/xendit/bills/{billId}/invoice', [\App\Http\Controllers\XenditInvoiceController::class, 'getByBill']);
Route::get('/payments', [\App\Http\Controllers\PaymentController::class, 'index']);
Route::get('/payments/{id}', [\App\Http\Controllers\PaymentController::class, 'show']);
Route::get('/bills/{billId}/payments', [\App\Http\Controllers\PaymentController::class, 'getByBill']);
Route::post('/xendit/callback', [\App\Http\Controllers\XenditCallbackController::class, 'handle']);
Route::get('/xendit/callbacks', [\App\Http\Controllers\XenditCallbackController::class, 'index']);
Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
Route::get('/users/{userId}/notifications', [\App\Http\Controllers\NotificationController::class, 'getByUser']);
Route::get('/users/{userId}/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'getUnread']);
Route::put('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
Route::put('/users/{userId}/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
Route::post('/notifications', [\App\Http\Controllers\NotificationController::class, 'create']);
Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy']);
Route::get('/reports', [\App\Http\Controllers\PaymentReportController::class, 'index']);
Route::get('/reports/{id}', [\App\Http\Controllers\PaymentReportController::class, 'show']);
Route::post('/reports/daily', [\App\Http\Controllers\PaymentReportController::class, 'generateDailyReport']);
Route::post('/reports/monthly', [\App\Http\Controllers\PaymentReportController::class, 'generateMonthlyReport']);
Route::get('/alerts', [\App\Http\Controllers\DueDateAlertController::class, 'index']);
Route::put('/alerts/{id}/processed', [\App\Http\Controllers\DueDateAlertController::class, 'markAsProcessed']);