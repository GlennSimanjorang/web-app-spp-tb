<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'month_year',
        'due_date',
        'amount',
        'total_paid',
        'status',
        'payment_categories_id',
        'student_id',
        'academic_years_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'total_paid' => 'float',
        'amount' => 'float',
    ];

    // Relasi
    public function paymentCategory()
    {
        return $this->belongsTo(PaymentCategory::class, 'payment_categories_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_years_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function dueDateAlerts()
    {
        return $this->hasMany(DueDateAlert::class);
    }

    public function paymentReports()
    {
        return $this->hasMany(PaymentReport::class);
    }
}
