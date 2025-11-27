<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\PaymentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use App\Formatter;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentReportController extends Controller
{
    /**
     * Menampilkan daftar laporan pembayaran.
     * Bisa difilter berdasarkan bill atau tanggal.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return Formatter::apiResponse(401, 'Unauthorized');
            }

            \Log::info('PaymentReport@index started', [
                'user_id' => $user->id,
                'role' => $user->role,
                'request' => $request->all(),
            ]);

            $query = PaymentReport::with('bill.student');

            if ($request->filled('bill_id')) {
                $query->where('bills_id', $request->bill_id);
            }

            if ($request->filled('from') && $request->filled('to')) {
                // Validasi tanggal
                $validator = Validator::make($request->all(), [
                    'from' => 'date',
                    'to' => 'date|after_or_equal:from',
                ]);

                if ($validator->fails()) {
                    return Formatter::apiResponse(422, 'Validasi tanggal gagal', $validator->errors());
                }

                $query->whereBetween('report_date', [$request->from, $request->to]);
            }

            if ($user->role === 'parents') {
                \Log::info('Filtering for parents', ['parent_user_id' => $user->id]);

                $query->whereHas('bill.student', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            $reports = $query->latest()->paginate(10);

            \Log::info('PaymentReport@index success', ['count' => $reports->total()]);

            return Formatter::apiResponse(200, 'Daftar laporan pembayaran', $reports);
        } catch (\Exception $e) {
            \Log::error('❌ PaymentReport@index FAILED', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'user_role' => Auth::user()?->role,
                'request' => $request->all(),
            ]);
            return Formatter::apiResponse(500, 'Terjadi kesalahan server');
        }
    }

    /**
     * Menampilkan satu laporan pembayaran.
     */
    public function show($id)
    {
        try {
            $paymentReport = PaymentReport::with('bill.student')->findOrFail($id);

            if (Auth::user()->role === 'parents') {
                if ($paymentReport->bill->student->user_id !== Auth::id()) {
                    return Formatter::apiResponse(403, 'Akses ditolak');
                }
            }

            return Formatter::apiResponse(200, 'Detail laporan pembayaran', $paymentReport);
        } catch (ModelNotFoundException $e) {
            return Formatter::apiResponse(404, 'Laporan pembayaran tidak ditemukan');
        } catch (\Exception $e) {
            return Formatter::apiResponse(500, 'Terjadi kesalahan server');
        }
    }

    /**
     * Membuat laporan pembayaran baru.
     */
    public function store(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            return Formatter::apiResponse(500, 'Terjadi kesalahan server');
        }
    }

    /**
     * Memperbarui laporan pembayaran.
     */
    public function update(Request $request, $id)
    {
        try {
            $paymentReport = PaymentReport::findOrFail($id);
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
        } catch (ModelNotFoundException $e) {
            return Formatter::apiResponse(404, 'Laporan pembayaran tidak ditemukan');
        } catch (\Exception $e) {
            return Formatter::apiResponse(500, 'Terjadi kesalahan server');
        }
    }

    /**
     * Menghapus laporan pembayaran.
     */
    public function destroy($id)
    {
        try {
            $paymentReport = PaymentReport::findOrFail($id);
            Gate::authorize('delete', $paymentReport);

            $paymentReport->delete();

            return Formatter::apiResponse(200, 'Laporan pembayaran dihapus');
        } catch (ModelNotFoundException $e) {
            return Formatter::apiResponse(404, 'Laporan pembayaran tidak ditemukan');
        } catch (\Exception $e) {
            return Formatter::apiResponse(500, 'Terjadi kesalahan server');
        }
    }
}
