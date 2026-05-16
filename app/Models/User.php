<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * The model does not use an updated_at column.
     */
    const UPDATED_AT = null;

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'admin_id', 'user_id');
    }

    public function secretary(): HasOne
    {
        return $this->hasOne(Secretary::class, 'secretary_id', 'user_id');
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class, 'doctor_id', 'user_id');
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class, 'patient_id', 'user_id');
    }
}
