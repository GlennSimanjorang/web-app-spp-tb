<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Daftar semua notifikasi (untuk admin)
     */
    public function index()
    {
        $notifications = Notification::with(['user', 'bill'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Formatter::apiResponse(200, 'Daftar notifikasi', $notifications);
    }

    /**
     * Notifikasi untuk satu pengguna (digunakan oleh frontend orang tua)
     */
    public function getByUser($userId)
    {
        $notifications = Notification::where('user_id', $userId)
            ->with(['user', 'bill'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

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
     * Tandai satu notifikasi sebagai sudah dibaca
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
     * Tandai semua notifikasi sebagai sudah dibaca
     */
    public function markAllAsRead($userId)
    {
        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return Formatter::apiResponse(200, 'Semua notifikasi ditandai sudah dibaca');
    }
}
