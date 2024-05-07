<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;
    public $guard_name = 'api';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'dob',
        'phone',
        'password',
        'is_active',
        'email_verified_at',
        'verification_token',
        'profile_image',
        'self_signup',
        'data'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'email_verified_at',
        'remember_token',
        'verification_token'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime:d/m/Y',
        'updated_at' => 'datetime:d/m/Y',
        // 'password' => 'hashed',
    ];
    public function sendPasswordResetNotification($token)
    {
        $url = Config::get('custom.frontend_url') . '/reset-password?token=' . $token;
        $this->notify(new ResetPasswordNotification($url));
    }
    public function scopeActive($query)
    {
        return  $query->where('is_active', true);
    }
}
