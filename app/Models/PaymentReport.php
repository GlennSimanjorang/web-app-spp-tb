<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentReport extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'report_period',
        'report_date',
        'total_amount',
        'total_transactions',
        'notes',
        'report_data',
        'academic_year_id',
        'payment_category_id',
        'start_date',
        'end_date'
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->id = (string) Str::uuid());
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function paymentCategory()
    {
        return $this->belongsTo(PaymentCategory::class, 'payment_category_id');
    }
}
