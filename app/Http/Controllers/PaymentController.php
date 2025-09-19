<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;
use App\Formatter;
use Midtrans\Notification;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Catat pembayaran manual (cash/transfer)
     */
    public function store(Request $request, Bill $bill)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,transfer,virtual_account',
            'amount_paid' => 'required|numeric|min:1|max:' . ($bill->amount - $bill->total_paid + 1),
            'payment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal', $validator->errors());
        }

        $data = $validator->validated();
        $data['processed_by'] = Auth::id();
        $data['bill_id'] = $bill->id;
        $data['status'] = in_array($data['payment_method'], ['cash', 'transfer']) ? 'success' : 'pending';

        try {
            DB::beginTransaction();

            $payment = Payment::create($data);

            if ($payment->status === 'success') {
                $this->updateBillStatus($bill);
            }

            DB::commit();

            return Formatter::apiResponse(201, 'Pembayaran berhasil dicatat.', $payment);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment Store Failed: ' . $e->getMessage(), ['exception' => $e]);
            return Formatter::apiResponse(500, 'Gagal menyimpan pembayaran.');
        }
    }

    /**
     * Buat transaksi Midtrans (SNAP)
     */
    public function createMidtransTransaction(Request $request, Bill $bill)
    {
        if (!$bill->student || !$bill->student->user) {
            return Formatter::apiResponse(400, 'Data siswa atau orang tua tidak lengkap.');
        }

        $outstanding = $bill->amount - $bill->total_paid;
        $amount = max(1, (int) $request->input('amount_paid', $outstanding));

        if ($amount < 1) {
            return Formatter::apiResponse(400, 'Jumlah pembayaran tidak valid.');
        }

        $orderId = 'PAY-' . $bill->id . '-' . time();

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $bill->student->name,
                'email' => $bill->student->user->email ?? 'anonymous@sekolah.com',
                'phone' => $bill->student->user->number ?? '-'
            ],
            'enabled_payments' => ['bank_transfer', 'gopay', 'qris', 'shopeepay', 'indomaret'],
        ];

        try {
            $snapToken = Snap::getSnapToken($payload);

            Payment::create([
                'payment_date' => now(),
                'amount_paid' => $amount,
                'payment_method' => 'virtual_account',
                'status' => 'pending',
                'midtrans_order_id' => $orderId,
                'processed_by' => Auth::id(),
                'bill_id' => $bill->id,
            ]);

            return Formatter::apiResponse(200, 'Transaksi Midtrans berhasil dibuat.', [
                'snap_token' => $snapToken,
                'order_id' => $orderId,
                'redirect_url' => "https://app.sandbox.midtrans.com/snap/v3/redirection/" . $snapToken,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Error: ' . $e->getMessage(), ['exception' => $e]);
            return Formatter::apiResponse(500, 'Gagal terhubung ke Midtrans.', [
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error.'
            ]);
        }
    }

    /**
     * Webhook dari Midtrans - VERSION FIXED
     */
    public function webhook(Request $request)
    {
        try {
            Log::info('Midtrans Webhook Received:', $request->all());

            // Handle different notification formats
            $notificationData = $request->all();

            // Extract data with fallbacks
            $orderId = $notificationData['order_id'] ?? null;
            $transactionStatus = $notificationData['transaction_status'] ?? null;
            $fraudStatus = $notificationData['fraud_status'] ?? 'accept';
            $paymentType = $notificationData['payment_type'] ?? null;
            $transactionId = $notificationData['transaction_id'] ?? null;

            // Handle VA numbers safely
            $vaNumber = null;
            if (isset($notificationData['va_numbers'][0])) {
                $vaNumberData = $notificationData['va_numbers'][0];
                $vaNumber = is_array($vaNumberData) ?
                    ($vaNumberData['va_number'] ?? null) :
                    ($vaNumberData->va_number ?? null);
            }

            Log::info('Midtrans Webhook Parsed:', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'payment_type' => $paymentType,
                'transaction_id' => $transactionId,
                'va_number' => $vaNumber
            ]);

            if (!$orderId) {
                Log::error('Midtrans Webhook Error: Missing order_id');
                return response()->json(['status' => 'Invalid order_id'], 400);
            }

            $payment = Payment::where('midtrans_order_id', $orderId)->first();

            if (!$payment) {
                Log::error('Midtrans Webhook Error: Payment not found for order_id: ' . $orderId);
                return response()->json(['status' => 'Payment not found'], 404);
            }

            // Hindari pengolahan ganda
            if ($payment->status === 'success' && in_array($transactionStatus, ['settlement', 'capture'])) {
                Log::info('Payment already processed: ' . $orderId);
                return response()->json(['status' => 'Already processed']);
            }

            $shouldUpdate = false;
            $newStatus = $payment->status;

            if (in_array($transactionStatus, ['capture', 'settlement']) && $fraudStatus === 'accept') {
                $newStatus = 'success';
                $shouldUpdate = true;
            } elseif ($transactionStatus === 'deny') {
                $newStatus = 'failed';
                $shouldUpdate = true;
            } elseif (in_array($transactionStatus, ['cancel', 'expire'])) {
                $newStatus = $transactionStatus;
                $shouldUpdate = true;
            } elseif ($transactionStatus === 'pending') {
                $newStatus = 'pending';
                $shouldUpdate = true;
            }

            if ($shouldUpdate) {
                $updateData = [
                    'status' => $newStatus,
                    'midtrans_transaction_id' => $transactionId,
                    'midtrans_payment_type' => $paymentType,
                    'midtrans_fraud_status' => $fraudStatus,
                    'midtrans_raw_response' => json_encode($notificationData),
                ];

                if ($vaNumber) {
                    $updateData['midtrans_va_number'] = $vaNumber;
                }

                DB::beginTransaction();
                try {
                    $payment->update($updateData);

                    if ($newStatus === 'success') {
                        $this->updateBillStatus($payment->bill);
                    }

                    DB::commit();
                    Log::info('Payment updated successfully: ' . $orderId . ' to status: ' . $newStatus);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Webhook DB Error: ' . $e->getMessage(), [
                        'exception' => $e,
                        'order_id' => $orderId
                    ]);
                }
            } else {
                Log::info('No status update needed for payment: ' . $orderId);
            }

            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook Processing Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            return response()->json(['status' => 'Error processing webhook'], 500);
        }
    }

    /**
     * Update status bill berdasarkan pembayaran sukses
     */
    private function updateBillStatus($bill)
    {
        $totalPaid = $bill->payments()->where('status', 'success')->sum('amount_paid');
        $bill->update(['total_paid' => $totalPaid]);

        if ($totalPaid >= $bill->amount) {
            $bill->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $bill->update(['status' => 'partial']);
        } else {
            if ($bill->due_date < today()) {
                $bill->update(['status' => 'overdue']);
            }
        }
    }
}
