<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'payment_date',
        'amount_paid',
        'payment_method',
        'reference_number',
        'xendit_payment_id',
        'xendit_external_id',
        'status',
        'callback_data',
        'processed_by',
        'bill_id',
        'xendit_virtual_account_id',
        'xendit_invoice_id'
    ];

    protected $casts = [
        'callback_data' => 'array',
        'payment_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->sqlid = Str::uuid());
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function virtualAccount()
    {
        return $this->belongsTo(XenditVirtualAccount::class, 'xendit_virtual_account_id');
    }

    public function xenditInvoice()
    {
        return $this->belongsTo(XenditInvoice::class, 'xendit_invoice_id');
    }

    public function callback()
    {
        return $this->hasOne(XenditCallback::class, 'payment_id');
    }
}
