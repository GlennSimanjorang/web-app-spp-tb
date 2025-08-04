<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class XenditCallback extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'callback_type',
        'xendit_id',
        'event_type',
        'raw_data',
        'is_processed',
        'processed_at',
        'payment_id'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->sqlid = Str::uuid());
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
