<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['bill.student', 'processedBy', 'virtualAccount', 'xenditInvoice'])->get();
        return Formatter::apiResponse(200, 'Daftar pembayaran', $payments);
    }

    public function show($id)
    {
        $payment = Payment::with(['bill.student', 'processedBy'])->find($id);
        if (!$payment) {
            return Formatter::apiResponse(404, 'Pembayaran tidak ditemukan');
        }
        return Formatter::apiResponse(200, 'Detail pembayaran', $payment);
    }

    public function getByBill($billId)
    {
        $payments = Payment::with('processedBy')->where('bill_id', $billId)->get();
        return Formatter::apiResponse(200, 'Pembayaran untuk tagihan ini', $payments);
    }
}
