<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bill extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'bill_number',
        'month_year',
        'due_date',
        'amount',
        'status',
        'payment_categories_id',
        'student_id',
        'academic_years_id'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->id = Str::uuid());
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function category()
    {
        return $this->belongsTo(PaymentCategory::class, 'payment_categories_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_years_id');
    }

    public function invoice()
    {
        return $this->hasOne(XenditInvoice::class, 'bill_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'bill_id');
    }

    public function alerts()
    {
        return $this->hasMany(DueDateAlert::class, 'bill_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'bill_id');
    }
}
