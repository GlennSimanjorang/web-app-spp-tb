<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class XenditInvoice extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'external_id',
        'xendit_invoice_id',
        'invoice_url',
        'status',
        'amount',
        'description',
        'customer_name',
        'customer_email',
        'customer_phone',
        'payment_methods',
        'bill_id',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->id = Str::uuid());
    }
    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'xendit_invoice_id');
    }
}
