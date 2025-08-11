<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\DueDateAlert;
use Illuminate\Http\Request;

class DueDateAlertController extends Controller
{
    public function index()
    {
        $alerts = DueDateAlert::with('bill.student')->get();
        return Formatter::apiResponse(200, 'Daftar jatuh tempo', $alerts);
    }

    public function markAsProcessed($id)
    {
        $alert = DueDateAlert::find($id);
        if (!$alert) {
            return Formatter::apiResponse(404, 'Alert tidak ditemukan');
        }

        $alert->update(['is_processed' => true]);
        return Formatter::apiResponse(200, 'ditandai telah diproses');
    }
}
