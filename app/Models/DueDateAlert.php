<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DueDateAlert extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'alert_type',
        'alert_date',
        'is_processed',
        'bill_id',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->sqlid = (string) Str::uuid());
    }
    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

}
