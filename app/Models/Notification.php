<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notification extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'title',
        'message',
        'type',
        'is_read',
        'user_id',
        'bill_id'
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->sqlid = Str::uuid());
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }
}
