<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_payment',
        'report_date', // pastikan ada di migration!
        'notes',
        'bills_id',
        'report_period',
        'start_date',
        'end_date',
        'total_amount',
        'total_transactions',
        'report_data',
        'academic_year_id',
        'payment_category_id',
    ];

    protected $casts = [
        'report_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'report_data' => 'array',
        'total_amount' => 'decimal:2',
    ];

    // Relasi
    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bills_id');
    }
}
