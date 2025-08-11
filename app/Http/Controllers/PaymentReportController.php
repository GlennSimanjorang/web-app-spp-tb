<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\PaymentReport;
use Illuminate\Http\Request;

class PaymentReportController extends Controller
{
    public function index()
    {
        $reports = PaymentReport::with(['academicYear', 'paymentCategory'])->get();
        return Formatter::apiResponse(200, 'Daftar laporan pembayaran', $reports);
    }

    public function show($id)
    {
        $report = PaymentReport::with(['academicYear', 'paymentCategory'])->find($id);
        if (!$report) {
            return Formatter::apiResponse(404, 'Laporan tidak ditemukan');
        }
        return Formatter::apiResponse(200, 'Detail laporan', $report);
    }
}
