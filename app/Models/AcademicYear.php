<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AcademicYear extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['school_year', 'start_date', 'end_date', 'is_active'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->id = Str::uuid());
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'academic_years_id');
    }

    public function reports()
    {
        return $this->hasMany(PaymentReport::class, 'academic_year_id');
    }
}
