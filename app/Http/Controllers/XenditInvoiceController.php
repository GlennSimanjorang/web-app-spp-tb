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

        if ($bill->invoice) {
            return Formatter::apiResponse(400, 'Invoice untuk tagihan ini sudah ada', $bill->invoice);
        }

        // Cek siapa yang login
        $currentUser = auth()->user();

        // Tentukan payer_email dan customer_name
        $payerEmail = $request->payer_email
            ?? $currentUser->email
            ?? $bill->student->user->email;

        $customerName = match ($currentUser->role) {
            'parent' => $currentUser->name,
            'admin' => $bill->student->name,
            default => $bill->student->name,
        };

        // Validasi opsional
        $validator = Validator::make($request->all(), [
            'payer_email' => 'nullable|email'
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal', null, $validator->errors());
        }

        $externalId = 'inv-' . $bill->id . '-' . uniqid('', true);

        $result = $this->xenditService->createInvoice(
            $externalId,
            $payerEmail,
            "Pembayaran tagihan {$bill->bill_number}",
            $bill->amount,
            $customerName  // tambahkan nama ke service
        );

        if (!$result['success']) {
            return Formatter::apiResponse(500, 'Gagal membuat invoice', null, $result['message']);
        }

        $invoiceData = $result['data'];

        $invoice = XenditInvoice::create([
            'id' => \Str::uuid(),
            'bill_id' => $bill->id,
            'external_id' => $invoiceData['external_id'],
            'xendit_invoice_id' => $invoiceData['id'],
            'status' => $invoiceData['status'],
            'amount' => $invoiceData['amount'],
            'invoice_url' => $invoiceData['invoice_url'],
            'expiry_date' => $invoiceData['expiry_date'] ?? null,
            'customer_name' => $customerName,
            'customer_email' => $payerEmail,
            'customer_phone' => $bill->student->user->phone_number ?? null,
        ]);

        return Formatter::apiResponse(201, 'Invoice berhasil dibuat', [
            'invoice' => $invoice,
            'invoice_url' => $invoice->invoice_url
        ]);
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
