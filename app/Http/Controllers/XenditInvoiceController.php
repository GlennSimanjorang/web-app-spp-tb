<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\XenditInvoice;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Bill;

class XenditInvoiceController extends Controller
{
    protected XenditService $xenditService;

    public function __construct(XenditService $xenditService)
    {
        $this->xenditService = $xenditService;
    }

    /**
     * Buat invoice baru dari bill_id
     */
    public function storeByBill(Request $request, $billId)
    {
        $bill = Bill::with('student.user')->findOrFail($billId);

        // Kalau invoice sudah ada, hentikan
        if ($bill->invoice) {
            return Formatter::apiResponse(400, 'Invoice untuk bill ini sudah ada', $bill->invoice);
        }

        // Validasi email (opsional)
        $validate = Validator::make($request->all(), [
            'payer_email' => 'nullable|email'
        ]);
        if ($validate->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal', $validate->errors());
        }

        $externalId = 'invoice-' . $bill->id . '-' . uniqid('', true);
        $payerEmail = $request->payer_email ?? $bill->student->user->email;

        $result = $this->xenditService->createInvoice(
            $externalId,
            $payerEmail,
            "Pembayaran tagihan {$bill->bill_number}",
            $bill->amount
        );

        if (!$result['success']) {
            return Formatter::apiResponse(500, 'Gagal membuat invoice', $result['message']);
        }

        $invoiceData = $result['data'];
        $invoice = XenditInvoice::create([
            'bill_id'       => $bill->id,
            'external_id'   => $invoiceData['external_id'],
            'invoice_id'    => $invoiceData['id'],
            'status'        => $invoiceData['status'],
            'amount'        => $invoiceData['amount'],
            'invoice_url'   => $invoiceData['invoice_url'],
            'expiry_date'   => $invoiceData['expiry_date'] ?? null,
        ]);

        return Formatter::apiResponse(201, 'Invoice berhasil dibuat', $invoice);
    }

    /**
     * Ambil invoice berdasarkan bill ID
     */
    public function getByBill($billId)
    {
        $invoice = XenditInvoice::with('bill')->where('bill_id', $billId)->first();

        if (!$invoice) {
            return Formatter::apiResponse(404, 'Invoice untuk tagihan ini tidak ditemukan');
        }

        return Formatter::apiResponse(200, 'Invoice ditemukan', $invoice);
    }

    /**
     * Ambil status invoice langsung dari Xendit
     */
    public function checkStatus($invoiceId)
    {
        $result = $this->xenditService->getInvoice($invoiceId);

        if (!$result['success']) {
            return Formatter::apiResponse(500, 'Gagal mengambil status invoice', $result['message']);
        }

        return Formatter::apiResponse(200, 'Status invoice berhasil diambil', $result['data']);
    }
}
