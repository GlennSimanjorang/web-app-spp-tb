<?php

namespace App\Models;

<<<<<<< HEAD
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
=======
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
>>>>>>> 48ceca89c80cd3cb95c2541bb2833327718bb572
        'email',
        'password',
    ];

<<<<<<< HEAD
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
=======
>>>>>>> 48ceca89c80cd3cb95c2541bb2833327718bb572
    protected $hidden = [
        'password',
        'remember_token',
    ];

<<<<<<< HEAD
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
=======
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
>>>>>>> 48ceca89c80cd3cb95c2541bb2833327718bb572
    }
}
