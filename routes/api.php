<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('academic-years', \App\Http\Controllers\AcademicYearController::class);
Route::apiResource('payment-categories', \App\Http\Controllers\PaymentCategoryController::class);
Route::apiResource('users', \App\Http\Controllers\UserController::class);
Route::apiResource('students', \App\Http\Controllers\StudentController::class);
Route::apiResource('bills', \App\Http\Controllers\BillController::class);
