<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\support\Str;

class XenditVirtualAccount extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'external_id',
        'account_number',
        'bank_code',
        'name',
        'is_closed',
        'expiration_date',
        'expected_amount',
        'bill_id'
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

    public function payments()
    {
        return $this->hasMany(Payment::class, 'xendit_virtual_account_id');
    }
}
