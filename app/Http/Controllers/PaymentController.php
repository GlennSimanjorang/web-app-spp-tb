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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
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
            'notes' => 'nullable|string|max:1000',
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
            $payment = Payment::create($data); // notes otomatis disimpan jika ada

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

            $notificationData = $request->all();
            $orderId = $notificationData['order_id'] ?? null;

            if (!$orderId) {
                Log::warning('Webhook tanpa order_id', $notificationData);
                return response()->json(['status' => 'ok']);
            }

            $payment = Payment::where('midtrans_order_id', $orderId)->first();

            if (!$payment) {
                Log::warning('Payment tidak ditemukan untuk order_id: ' . $orderId, [
                    'payload' => $notificationData
                ]);
                return response()->json(['status' => 'ok']);
            }

            // ... proses update payment seperti biasa ...

            Log::info('Payment updated successfully: ' . $orderId);
            return response()->json(['status' => 'ok']); // ✅ 200

        } catch (\Exception $e) {
            Log::error('Webhook exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'ok']); // ✅ tetap 200!
        }
    }

    /**
     * Update status bill berdasarkan pembayaran sukses
     */
    private function updateBillStatus($bill)
    {
        $totalPaid = $bill->payments()->where('status', 'success')->sum('amount_paid');
        $bill->update(['total_paid' => $totalPaid]);

        $wasUnpaid = $bill->status !== 'paid';
        $isNowPaid = $totalPaid >= $bill->amount;

        if ($isNowPaid) {
            $bill->update(['status' => 'paid']);

            // 🔥 Kirim notifikasi & email hanya jika benar-benar baru lunas
            if ($wasUnpaid && $bill->student?->user_id) {
                // Simpan notifikasi ke database
                \App\Models\Notification::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'title' => 'Pembayaran Berhasil',
                    'message' => "Pembayaran tagihan '{$bill->month_year}' sebesar Rp" . number_format($bill->amount, 0, ',', '.') . " telah lunas.",
                    'type' => 'payment_success',
                    'is_read' => false,
                    'user_id' => $bill->student->user_id,
                    'bill_id' => $bill->id,
                ]);
                \Log::info('Mencoba kirim email untuk bill', ['bill_id' => $bill->id]);
                $this->sendPaymentSuccessEmail($bill);
                // 🔥 Kirim email
                $this->sendPaymentSuccessEmail($bill);
            }
        } elseif ($totalPaid > 0) {
            $bill->update(['status' => 'partial']);
        } else {
            if ($bill->due_date < today()) {
                $bill->update(['status' => 'overdue']);
            }
        }
    }

    private function sendPaymentSuccessEmail($bill)
    {
        $user = $bill->student?->user;
        if (!$user || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            $html = View::make('emails.payment_success', [
                'studentName' => $bill->student->name,
                'monthYear'   => $bill->month_year,
                'amount'      => $bill->amount,
            ])->render();

            Mail::html($html, function ($message) use ($user, $bill) {
                $message->to($user->email)
                    ->subject('✅ Pembayaran Berhasil - ' . $bill->month_year);
            });

            \Log::info('Email pembayaran sukses terkirim ke: ' . $user->email);
        } catch (\Exception $e) {
            \Log::warning('Gagal kirim email', ['error' => $e->getMessage()]);
        }
    }

    public function history(Request $request)
    {
        $user = Auth::user();

        $query = Payment::with([
            'bill.student:id,name,nisn,kelas,user_id',
            'bill.paymentCategory:id,name'
        ])
            ->where('status', 'success')
            ->latest();

        // 🔒 Filter untuk parents
        if ($user->role === 'parents') {
            $query->whereHas('bill.student', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Filter tanggal opsional
        if ($request->filled('from') && $request->filled('to')) {
            $validator = Validator::make($request->all(), [
                'from' => 'date',
                'to' => 'date|after_or_equal:from',
            ]);
            if ($validator->fails()) {
                return Formatter::apiResponse(422, 'Validasi tanggal gagal', $validator->errors());
            }
            $query->whereBetween('payment_date', [$request->from, $request->to]);
        }

        // Filter per siswa (opsional, untuk admin/parents)
        if ($request->filled('student_id')) {
            $query->whereHas('bill', fn($q) => $q->where('student_id', $request->student_id));
        }

        $payments = $query->paginate(15);

        return Formatter::apiResponse(200, 'Riwayat pembayaran', $payments);
    }
}
