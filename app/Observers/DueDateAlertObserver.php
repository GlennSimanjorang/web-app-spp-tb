<?php

namespace App\Observers;

use App\Models\DueDateAlert;
use App\Models\Notification;
use Illuminate\Support\Str;

class DueDateAlertObserver
{
    /**
     * Handle the DueDateAlert "created" event.
     */
    public function created(DueDateAlert $alert)
    {
        $bill = $alert->bill;
        $user_id = $bill->student->user_id;

        

        Notification::create([
            'id' => Str::uuid(),
            'user_id' => $user_id,
            'bill_id' => $bill->id,
            'title' => match ($alert->alert_type) {
                'upcoming' => 'Pengingat Pembayaran',
                'due' => 'Tagihan Jatuh Tempo Hari Ini!',
                default => 'Peringatan Tagihan'
            },
            'message' => "Tagihan {$bill->month_year} jatuh tempo pada " . $alert->alert_date->format('d F Y'),
            'type' => 'payment_reminder',
            'is_read' => false,
        ]);

        if (Notification::where('bill_id', $bill->id)
            ->where('type', 'payment_reminder')
            ->exists()
        ) {
            return;
        }
        
    }

    /**
     * Handle the DueDateAlert "updated" event.
     */
    public function updated(DueDateAlert $dueDateAlert): void
    {
        //
    }

    /**
     * Handle the DueDateAlert "deleted" event.
     */
    public function deleted(DueDateAlert $dueDateAlert): void
    {
        //
    }

    /**
     * Handle the DueDateAlert "restored" event.
     */
    public function restored(DueDateAlert $dueDateAlert): void
    {
        //
    }

    /**
     * Handle the DueDateAlert "force deleted" event.
     */
    public function forceDeleted(DueDateAlert $dueDateAlert): void
    {
        //
    }
}
