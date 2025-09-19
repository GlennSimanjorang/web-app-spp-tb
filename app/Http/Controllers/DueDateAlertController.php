<?php

namespace App\Http\Controllers;

use App\Models\DueDateAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DueDateAlertController extends Controller
{
    /**
     * Ambil daftar alert.
     * Filter: belum diproses, berdasarkan user (jika orang tua)
     */
    public function index(Request $request)
    {
        $query = DueDateAlert::with('bill.student.academicYear', 'bill.paymentCategory')
            ->where(function ($q) {
                $q->where('is_processed', false)->orWhereNull('is_processed');
            });

        // Jika role parents, hanya lihat milik anaknya
        if (auth()->user()->role === 'parents') {
            $query->whereHas('bill.student.user', fn($q) => $q->where('id', auth()->id()));
        }

        // Filter by type
        if ($request->filled('alert_type')) {
            $query->where('alert_type', $request->alert_type);
        }

        $alerts = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $alerts
        ]);
    }

    /**
     * Detail satu alert
     */
    public function show(DueDateAlert $dueDateAlert)
    {
        // Cek akses: apakah milik anak user ini?
        if (auth()->user()->role === 'parents') {
            $isAllowed = $dueDateAlert->bill->student->user_id === auth()->id();
            if (!$isAllowed) {
                abort(403, 'Akses ditolak.');
            }
        }

        $dueDateAlert->load('bill.student', 'bill.paymentCategory');

        return response()->json([
            'success' => true,
            'data' => $dueDateAlert
        ]);
    }

    /**
     * Update status alert (misal: tandai sudah diproses)
     */
    public function update(Request $request, DueDateAlert $dueDateAlert)
    {
        Gate::authorize('update', $dueDateAlert);

        $validated = $request->validate([
            'is_processed' => 'required|boolean',
            'alert_type' => 'sometimes|in:upcoming,overdue,critical'
        ]);

        $dueDateAlert->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Status alert diperbarui.',
            'data' => $dueDateAlert
        ]);
    }

    /**
     * Hapus alert (opsional - biasanya tidak perlu)
     */
    public function destroy(DueDateAlert $dueDateAlert)
    {
        Gate::authorize('delete', $dueDateAlert);

        // Opsional: cegah hapus jika terkait notifikasi
        $dueDateAlert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alert dihapus.'
        ]);
    }
}
