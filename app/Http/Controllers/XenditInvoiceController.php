<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\XenditInvoice;
use Illuminate\Http\Request;

class XenditInvoiceController extends Controller
{
    public function show($id)
    {
        $invoice = XenditInvoice::with('bill.student')->find($id);
        if (!$invoice) {
            return Formatter::apiResponse(404, 'Invoice tidak ditemukan');
        }
        return Formatter::apiResponse(200, 'Detail Invoice', $invoice);
    }

    public function getByBill($billId)
    {
        $invoice = XenditInvoice::with('bill')->where('bill_id', $billId)->first();
        if (!$invoice) {
            return Formatter::apiResponse(404, 'Invoice untuk tagihan ini tidak ditemukan');
        }
        return Formatter::apiResponse(200, 'Invoice ditemukan', $invoice);
    }
}
