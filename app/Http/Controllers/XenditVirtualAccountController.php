<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use App\Models\XenditVirtualAccount;
use Xendit\Xendit;
use Xendit\VirtualAccounts\VAParameters;
use App\Formatter;
use Illuminate\Http\Request;
use App\Models\Bill;

class XenditVirtualAccountController extends Controller
{
    public function __construct()
    {
        
        }

    /**
     * Buat Virtual Account untuk tagihan
     */
    public function createVirtualAccount(Request $request, $billId)
    {
        $bill = Bill::with('student')->find($billId);

        if (!$bill) {
            return Formatter::apiResponse(404, 'Tagihan tidak ditemukan');
        }

        if ($bill->status === 'paid') {
            return Formatter::apiResponse(400, 'Tagihan sudah dibayar');
        }

        if ($bill->virtualAccount) {
            return Formatter::apiResponse(200, 'Virtual Account sudah ada', $bill->virtualAccount);
        }

        try {
            $params = new VAParameters([
                'external_id' => 'va-' . $bill->id,
                'bank_code' => $request->bank_code ?? 'BNI',
                'name' => $bill->student->name,
                'expected_amount' => (float)$bill->amount,
                'is_closed' => true,
                'expiration_date' => now()->addDays(1)->toISOString(),
            ]);

            $xenditVA = \Xendit\VirtualAccounts::create($params);

            $va = XenditVirtualAccount::create([
                'id' => Str::uuid(),
                'external_id' => $xenditVA['external_id'],
                'account_number' => $xenditVA['account_number'],
                'bank_code' => $xenditVA['bank_code'],
                'name' => $xenditVA['name'],
                'is_closed' => $xenditVA['is_closed'],
                'expiration_date' => $xenditVA['expiration_date'],
                'expected_amount' => $xenditVA['expected_amount'],
                'bill_id' => $bill->id,
            ]);

            return Formatter::apiResponse(201, 'Virtual Account berhasil dibuat', [
                'va' => $va,
                'account_number' => $xenditVA['account_number'],
                'bank_code' => $xenditVA['bank_code'],
                'amount' => $xenditVA['expected_amount'],
                'expiration' => $xenditVA['expiration_date']
            ]);
        } catch (\Exception $e) {
            return Formatter::apiResponse(500, 'Gagal buat VA', null, $e->getMessage());
        }
    }

    /**
     * Tampilkan detail VA
     */
    public function show($id)
    {
        $va = XenditVirtualAccount::with('bill.student')->find($id);
        if (!$va) {
            return Formatter::apiResponse(404, 'Virtual Account tidak ditemukan');
        }
        return Formatter::apiResponse(200, 'Detail VA', $va);
    }

    /**
     * Dapatkan VA berdasarkan bill
     */
    public function getByBill($billId)
    {
        $va = XenditVirtualAccount::with('bill')->where('bill_id', $billId)->first();
        if (!$va) {
            return Formatter::apiResponse(404, 'VA untuk tagihan ini tidak ditemukan');
        }
        return Formatter::apiResponse(200, 'VA ditemukan', $va);
    }
}
