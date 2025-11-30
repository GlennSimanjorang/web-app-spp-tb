<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Daftar semua notifikasi — HANYA untuk admin
     */
    public function index()
    {
        // Pastikan hanya admin yang bisa akses ini
        if (Auth::user()->role !== 'admin') {
            return Formatter::apiResponse(403, 'Akses ditolak.');
        }

        $notifications = Notification::with(['user', 'bill'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Formatter::apiResponse(200, 'Daftar notifikasi', $notifications);
    }

    /**
     * Notifikasi milik pengguna yang sedang login (untuk orang tua)
     */
    public function myNotifications(Request $request)
    {
        $userId = Auth::id();

        $notifications = Notification::where('user_id', $userId)
            ->with(['user', 'bill'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Formatter::apiResponse(200, 'Notifikasi Anda', $notifications);
    }

    /**
     * Notifikasi belum dibaca milik pengguna yang sedang login
     */
    public function unread()
    {
        $userId = Auth::id();

        $notifications = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->with(['user', 'bill'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Formatter::apiResponse(200, 'Notifikasi belum dibaca', $notifications);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca — hanya jika milik user sendiri
     */
    public function markAsRead($id)
    {
        $userId = Auth::id();

        $notification = Notification::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return Formatter::apiResponse(404, 'Notifikasi tidak ditemukan atau bukan milik Anda.');
        }

        $notification->update(['is_read' => true]);

        return Formatter::apiResponse(200, 'Notifikasi ditandai sudah dibaca');
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca — hanya milik user sendiri
     */
    public function markAllAsRead()
    {
        $userId = Auth::id();

        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return Formatter::apiResponse(200, 'Semua notifikasi ditandai sudah dibaca');
    }
}
