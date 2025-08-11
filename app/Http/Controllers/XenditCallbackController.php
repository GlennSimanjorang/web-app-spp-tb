<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\XenditCallback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class XenditCallbackController extends Controller
{
    /**
     * Handle webhook dari Xendit
     */
    public function handle(Request $request)
    {
        // Ambil data callback
        $event = $request->input('event');
        $data = $request->all();

        // Log raw callback untuk debugging
        Log::info('Xendit Callback Received', [
            'event' => $event,
            'data' => $data,
            'ip' => $request->ip(),
        ]);

        // Simpan callback ke database
        $callback = XenditCallback::create([
            'id' => Str::uuid(),
            'callback_type' => $this->getCallbackType($event),
            'xendit_id' => $data['id'] ?? null,
            'event_type' => $event,
            'raw_data' => $data,
            'is_processed' => false,
        ]);

        try {
            // Proses berdasarkan event
            switch ($event) {
                case 'virtual_account.paid':
                    $this->handleVirtualAccountPaid($data, $callback);
                    break;

                case 'invoice.paid':
                case 'invoice.settled':
                    $this->handleInvoicePaid($data, $callback);
                    break;

                case 'virtual_account.expired':
                    $this->handleVirtualAccountExpired($data, $callback);
                    break;

                case 'invoice.expired':
                    $this->handleInvoiceExpired($data, $callback);
                    break;

                default:
                    Log::warning('Unhandled Xendit event', ['event' => $event]);
                    $callback->update(['is_processed' => true]);
                    break;
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Error processing Xendit callback', [
                'exception' => $e->getMessage(),
                'data' => $data,
            ]);

            // Jangan return 4xx karena Xendit akan terus retry
            // Cukup log dan return 200
            return response('Processing failed, but acknowledged', 200);
        }
    }

    /**
     * Cek tipe callback berdasarkan event
     */
    private function getCallbackType(string $event): string
    {
        if (str_starts_with($event, 'virtual_account.')) {
            return 'virtual_account';
        } elseif (str_starts_with($event, 'invoice.')) {
            return 'invoice';
        } elseif (str_starts_with($event, 'ewallet.')) {
            return 'ewallet';
        } elseif (str_starts_with($event, 'qris.')) {
            return 'qris';
        }
        return 'other';
    }

    /**
     * Proses: Virtual Account Dibayar
     */
    private function handleVirtualAccountPaid(array $data, XenditCallback $callback)
    {
        $externalId = $data['external_id'] ?? null;
        $amount = $data['amount'] ?? 0;

        if (!$externalId) {
            Log::warning('VA Paid: external_id not found', $data);
            $callback->update(['is_processed' => true]);
            return;
        }

        // Cari bill berdasarkan VA
        $bill = Bill::whereHas('virtualAccount', function ($q) use ($externalId) {
            $q->where('external_id', $externalId);
        })->first();

        if (!$bill) {
            Log::warning('Bill not found for VA payment', ['external_id' => $externalId]);
            $callback->update(['is_processed' => true]);
            return;
        }

        // Cek apakah sudah dibayar
        if ($bill->status === 'paid') {
            $callback->update(['is_processed' => true]);
            Log::info('Payment ignored: bill already paid', ['bill_id' => $bill->id]);
            return;
        }

        // Cek nominal
        if ($amount < $bill->amount) {
            Log::warning('Payment amount too low', [
                'expected' => $bill->amount,
                'received' => $amount,
                'bill_id' => $bill->id
            ]);
            $callback->update(['is_processed' => true]);
            return;
        }

        // Buat pembayaran
        $payment = Payment::create([
            'id' => Str::uuid(),
            'payment_date' => now(),
            'amount_paid' => $amount,
            'payment_method' => 'virtual_account',
            'xendit_payment_id' => $data['payment_id'] ?? null,
            'xendit_external_id' => $externalId,
            'status' => 'settled',
            'bill_id' => $bill->id,
            'xendit_virtual_account_id' => $bill->virtualAccount->id,
            'callback_data' => $data,
        ]);

        // Update status bill
        $bill->update(['status' => 'paid']);

        // Update callback
        $callback->update([
            'payment_id' => $payment->id,
            'is_processed' => true
        ]);

        // 📬 Di sini kamu bisa tambahkan: kirim notifikasi ke orang tua
        // Notification::create([...])

        Log::info('VA Payment processed successfully', [
            'bill_id' => $bill->id,
            'payment_id' => $payment->id,
            'amount' => $amount
        ]);
    }

    /**
     * Proses: Invoice Dibayar
     */
    private function handleInvoicePaid(array $data, XenditCallback $callback)
    {
        $externalId = $data['external_id'] ?? null;

        if (!$externalId) {
            Log::warning('Invoice Paid: external_id not found', $data);
            $callback->update(['is_processed' => true]);
            return;
        }

        $bill = Bill::whereHas('invoice', function ($q) use ($externalId) {
            $q->where('external_id', $externalId);
        })->first();

        if (!$bill || $bill->status === 'paid') {
            $callback->update(['is_processed' => true]);
            return;
        }

        $amount = $data['amount'] ?? $bill->amount;

        if ($amount < $bill->amount) {
            Log::warning('Invoice payment amount too low', [
                'expected' => $bill->amount,
                'received' => $amount
            ]);
            $callback->update(['is_processed' => true]);
            return;
        }

        $payment = Payment::create([
            'id' => Str::uuid(),
            'payment_date' => now(),
            'amount_paid' => $amount,
            'payment_method' => 'invoice', // atau parse dari payment_channel
            'xendit_payment_id' => $data['id'] ?? null,
            'xendit_external_id' => $externalId,
            'status' => 'settled',
            'bill_id' => $bill->id,
            'xendit_invoice_id' => $bill->invoice->id,
            'callback_data' => $data,
        ]);

        $bill->update(['status' => 'paid']);

        $callback->update([
            'payment_id' => $payment->id,
            'is_processed' => true
        ]);

        Log::info('Invoice Payment processed', ['bill_id' => $bill->id]);
    }

    /**
     * Proses: VA Kadaluarsa
     */
    private function handleVirtualAccountExpired(array $data, XenditCallback $callback)
    {
        $externalId = $data['external_id'] ?? null;

        if ($externalId) {
            $bill = Bill::whereHas('virtualAccount', function ($q) use ($externalId) {
                $q->where('external_id', $externalId);
            })->first();

            if ($bill && $bill->status === 'unpaid') {
                // Bisa update status jadi 'overdue' atau biarkan saja
                Log::info('VA Expired', ['bill_id' => $bill->id]);
            }
        }

        $callback->update(['is_processed' => true]);
    }

    /**
     * Proses: Invoice Kadaluarsa
     */
    private function handleInvoiceExpired(array $data, XenditCallback $callback)
    {
        $externalId = $data['external_id'] ?? null;

        if ($externalId) {
            $bill = Bill::whereHas('invoice', function ($q) use ($externalId) {
                $q->where('external_id', $externalId);
            })->first();

            if ($bill && $bill->status === 'unpaid') {
                Log::info('Invoice Expired', ['bill_id' => $bill->id]);
            }
        }

        $callback->update(['is_processed' => true]);
    }

    /**
     * [Optional] Tampilkan daftar callback (untuk monitoring)
     */
    public function index()
    {
        $callbacks = XenditCallback::with('payment')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Formatter::apiResponse(200, 'Daftar callback dari Xendit', $callbacks);
    }
}
