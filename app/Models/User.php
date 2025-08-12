<?php

namespace App\Models;


use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'role',
        'phone_number',
        'password',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => $model->id = Str::uuid());
    }

    public function student()
    {
        return $this->hasMany(Student::class,  'id');
    }
    public function paymentsProcessed()
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
    


}
