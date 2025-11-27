<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'frequency',
    ];

    // Relasi
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
