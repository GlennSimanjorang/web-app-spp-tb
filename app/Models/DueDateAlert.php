<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DueDateAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_type',
        'alert_date',
        'is_processed',
        'bill_id',
    ];

    protected $casts = [
        'alert_date' => 'date',
        'is_processed' => 'boolean',
    ];

    // Relasi
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
