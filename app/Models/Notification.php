<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'is_read',
        'user_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bill()
    {
        return $this->belongsTo(\App\Models\Bill::class, 'bill_id');
    }
}
