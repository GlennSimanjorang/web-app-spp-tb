<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\Bill;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /**
     * Daftar semua notifikasi
     */
    public function index()
    {
        $notifications = Notification::with(['user', 'bill'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Formatter::apiResponse(200, 'Daftar notifikasi', $notifications);
    }

    /**
     * Notifikasi untuk satu pengguna
     */
    public function getByUser($userId)
    {
        $notifications = Notification::where('user_id', $userId)
            ->with(['user', 'bill'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Formatter::apiResponse(200, 'Notifikasi pengguna', $notifications);
    }

    /**
     * Notifikasi belum dibaca
     */
    public function getUnread($userId)
    {
        $notifications = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->with(['user', 'bill'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Formatter::apiResponse(200, 'Notifikasi belum dibaca', $notifications);
    }

    /**
     * Tandai satu notifikasi sebagai dibaca
     */
    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return Formatter::apiResponse(404, 'Notifikasi tidak ditemukan');
        }

        $notification->update(['is_read' => true]);

        return Formatter::apiResponse(200, 'Notifikasi ditandai sudah dibaca');
    }

    /**
     * Tandai semua notifikasi sebagai dibaca
     */
    public function markAllAsRead(Request $request, $userId)
    {
        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return Formatter::apiResponse(200, 'Semua notifikasi ditandai sudah dibaca');
    }

    /**
     * Buat notifikasi manual (untuk testing atau sistem)
     */
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string',
            'type' => 'required|in:payment_reminder,payment_success,payment_failed,va_created,invoice_created',
            'user_id' => 'required|string|exists:users,id',
            'bill_id' => 'nullable|string|exists:bills,id'
        ]);

        $notification = Notification::create([
            'id' => Str::uuid(),
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'is_read' => false,
            'user_id' => $request->user_id,
            'bill_id' => $request->bill_id,
        ]);

        return Formatter::apiResponse(201, 'Notifikasi berhasil dibuat', $notification);
    }

    /**
     * Hapus notifikasi
     */
    public function destroy($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return Formatter::apiResponse(404, 'Notifikasi tidak ditemukan');
        }

        $notification->delete();
        return Formatter::apiResponse(200, 'Notifikasi dihapus');
    }
}
