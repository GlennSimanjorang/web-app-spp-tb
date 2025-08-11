<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\XenditVirtualAccount;
use Illuminate\Http\Request;

class XenditVirtualAccountController extends Controller
{
    public function show($id)
    {
        $va = XenditVirtualAccount::with('bill.student')->find($id);
        if (!$va) {
            return Formatter::apiResponse(404, 'Virtual Account tidak ditemukan');
        }
        return Formatter::apiResponse(200, 'Detail VA', $va);
    }

    public function getByBill($billId)
    {
        $va = XenditVirtualAccount::with('bill')->where('bill_id', $billId)->first();
        if (!$va) {
            return Formatter::apiResponse(404, 'VA untuk tagihan ini tidak ditemukan');
        }
        return Formatter::apiResponse(200, 'VA ditemukan', $va);
    }
}
