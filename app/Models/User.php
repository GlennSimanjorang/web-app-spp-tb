<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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
