<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_date',
        'amount_paid',
        'payment_method',
        'reference_number',
        'receipt_of_payment',
        'status',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'midtrans_va_number',
        'midtrans_fraud_status',
        'midtrans_raw_response',
        'processed_by',
        'bill_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'midtrans_raw_response' => 'array', // JSON otomatis jadi array
    ];

    // Relasi
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
