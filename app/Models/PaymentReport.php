<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_payment',
        'report_date',
        'notes',
        'bills_id',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    // Relasi
    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bills_id');
    }
}
