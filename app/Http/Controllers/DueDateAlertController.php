<?php

namespace App\Http\Controllers;

use App\Models\DueDateAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;        // Untuk Auth::user(), Auth::id()
use Illuminate\Support\Facades\Validator;   // Untuk validasi
use Illuminate\Support\Facades\Log;         // Untuk logging
use App\Formatter;                          // Untuk Formatter::apiResponse()

class DueDateAlertController extends Controller
{
    /**
     * Ambil daftar alert.
     * Filter: berdasarkan rentang tanggal, hanya milik anak user (jika orang tua)
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return Formatter::apiResponse(401, 'Unauthorized');
            }

            \Log::info('DueDateAlert@index started', [
                'user_id' => $user->id,
                'role' => $user->role,
                'request' => $request->all(),
            ]);

            $query = DueDateAlert::with('bill.student');

            // Filter by date range
            if ($request->filled('from') && $request->filled('to')) {
                $validator = Validator::make($request->all(), [
                    'from' => 'date',
                    'to' => 'date|after_or_equal:from',
                ]);

                if ($validator->fails()) {
                    return Formatter::apiResponse(422, 'Validasi tanggal gagal', $validator->errors());
                }

                $query->whereBetween('alert_date', [$request->from, $request->to]);
            }

            // Jika user adalah orang tua, hanya tampilkan alert untuk anaknya
            if ($user->role === 'parents') {
                \Log::info('Filtering for parents', ['parent_user_id' => $user->id]);

                $query->whereHas('bill.student', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            $alerts = $query->latest()->paginate(10);

            \Log::info('DueDateAlert@index success', ['count' => $alerts->total()]);

            return Formatter::apiResponse(200, 'Daftar pengingat jatuh tempo', $alerts);
        } catch (\Exception $e) {
            \Log::error('❌ DueDateAlert@index FAILED', [
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
     * Detail satu alert
     */
    public function show(DueDateAlert $dueDateAlert)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return Formatter::apiResponse(401, 'Unauthorized');
            }

            // Cek akses: apakah milik anak user ini?
            if ($user->role === 'parents') {
                $isAllowed = $dueDateAlert->bill->student->user_id === $user->id;
                if (!$isAllowed) {
                    return Formatter::apiResponse(403, 'Akses ditolak.');
                }
            }

            $dueDateAlert->load('bill.student', 'bill.paymentCategory');

            return Formatter::apiResponse(200, 'Detail pengingat jatuh tempo', $dueDateAlert);
        } catch (\Exception $e) {
            \Log::error('❌ DueDateAlert@show FAILED', [
                'message' => $e->getMessage(),
                'due_date_alert_id' => $dueDateAlert->id ?? null,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            return Formatter::apiResponse(500, 'Terjadi kesalahan server');
        }
    }

    /**
     * Update status alert (misal: tandai sudah diproses)
     */
    public function update(Request $request, DueDateAlert $dueDateAlert)
    {
        try {
            Gate::authorize('update', $dueDateAlert);

            $validator = Validator::make($request->all(), [
                'is_processed' => 'required|boolean',
                'alert_type' => 'sometimes|in:upcoming,overdue,critical',
            ]);

            if ($validator->fails()) {
                return Formatter::apiResponse(422, 'Validasi gagal', $validator->errors());
            }

            $dueDateAlert->update($validator->validated());

            return Formatter::apiResponse(200, 'Status alert diperbarui.', $dueDateAlert);
        } catch (\Exception $e) {
            \Log::error('❌ DueDateAlert@update FAILED', [
                'message' => $e->getMessage(),
                'due_date_alert_id' => $dueDateAlert->id,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            return Formatter::apiResponse(500, 'Terjadi kesalahan server');
        }
    }

    /**
     * Hapus alert (opsional - biasanya tidak perlu)
     */
    public function destroy(DueDateAlert $dueDateAlert)
    {
        try {
            Gate::authorize('delete', $dueDateAlert);

            $dueDateAlert->delete();

            return Formatter::apiResponse(200, 'Alert dihapus.');
        } catch (\Exception $e) {
            \Log::error(' DueDateAlert@destroy FAILED', [
                'message' => $e->getMessage(),
                'due_date_alert_id' => $dueDateAlert->id,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            return Formatter::apiResponse(500, 'Terjadi kesalahan server');
        }
    }
}
