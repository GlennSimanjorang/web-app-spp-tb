<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_years',
        'start_date',
        'end_date',
        'is_active',
    ];

    // Relasi
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
