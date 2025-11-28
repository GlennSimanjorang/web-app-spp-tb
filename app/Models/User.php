<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'role',
        'number',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function paymentsProcessed()
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
