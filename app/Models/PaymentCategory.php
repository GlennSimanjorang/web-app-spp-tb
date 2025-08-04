<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentCategory extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['name', 'amount', 'frequency', 'description', 'is_active'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->id = Str::uuid());
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'payment_categories_id');
    }

    public function reports()
    {
        return $this->hasMany(PaymentReport::class, 'payment_category_id');
    }
}
