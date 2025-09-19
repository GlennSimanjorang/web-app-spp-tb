<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\PaymentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Formatter;

class PaymentReportController extends Controller
{
    /**
     * Menampilkan daftar laporan pembayaran.
     * Bisa difilter berdasarkan bill atau tanggal.
     */
    public function index(Request $request)
    {
        $query = PaymentReport::with('bill.student');

        if ($request->filled('bill_id')) {
            $query->where('bills_id', $request->bill_id);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('report_date', [$request->from, $request->to]);
        }

        if (Auth::user()->role === 'parents') {
            $query->whereHas('bill.student', fn($q) => $q->where('user_id', Auth::id()));
        }

        $reports = $query->latest()->paginate(10);

        return Formatter::apiResponse(200, 'Daftar laporan pembayaran', $reports);
    }

    /**
     * Menampilkan satu laporan pembayaran.
     */
    public function show(PaymentReport $paymentReport = null)
    {
        if (!$paymentReport) {
            return Formatter::apiResponse(404, 'Laporan pembayaran tidak ditemukan');
        }

        if (Auth::user()->role === 'parents') {
            $isAllowed = $paymentReport->bill->student->user_id === Auth::id();
            if (!$isAllowed) {
                return Formatter::apiResponse(403, 'Akses ditolak');
            }
        }

        $paymentReport->load('bill.student');

        return Formatter::apiResponse(200, 'Detail laporan pembayaran', $paymentReport);
    }

    /**
     * Membuat laporan pembayaran baru.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', PaymentReport::class);

        $validator = Validator::make($request->all(), [
            'report_payment' => 'required|string|max:255',
            'report_date' => 'required|date',
            'notes' => 'nullable|string',
            'bills_id' => 'required|exists:bills,id',
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal', $validator->errors());
        }

        $report = PaymentReport::create($validator->validated());

        return Formatter::apiResponse(201, 'Laporan pembayaran berhasil dibuat', $report);
    }

    /**
     * Memperbarui laporan pembayaran.
     */
    public function update(Request $request, PaymentReport $paymentReport = null)
    {
        if (!$paymentReport) {
            return Formatter::apiResponse(404, 'Laporan pembayaran tidak ditemukan');
        }

        Gate::authorize('update', $paymentReport);

        $validator = Validator::make($request->all(), [
            'report_payment' => 'sometimes|required|string|max:255',
            'report_date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal', $validator->errors());
        }

        $paymentReport->update($validator->validated());

        return Formatter::apiResponse(200, 'Laporan pembayaran diperbarui', $paymentReport);
    }

    /**
     * Menghapus laporan pembayaran.
     */
    public function destroy(PaymentReport $paymentReport = null)
    {
        if (!$paymentReport) {
            return Formatter::apiResponse(404, 'Laporan pembayaran tidak ditemukan');
        }

        Gate::authorize('delete', $paymentReport);

        $paymentReport->delete();

        return Formatter::apiResponse(200, 'Laporan pembayaran dihapus');
    }
}
