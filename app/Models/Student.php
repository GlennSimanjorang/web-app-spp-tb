<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Student extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
    'name', 
    'nisn', 
    'kelas', 
    'user_id'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->id = Str::uuid());
    }

    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'student_id');
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Bill::class, 'student_id', 'bill_id');
    }
}
