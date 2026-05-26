<?php

namespace App\Models;

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
        'user_type',
        'first_name',
        'last_name',
        'email',
        'mobile_number',
        'username',
        'password',
        'address',
        'zip_code',
        'date_of_birth',
        'password_reset_token',
        'password_reset_expires',
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
    protected $appends = ['name', 'phone'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            if ($user->user_type === 'admin') {
                $user->adminPreference()->create([
                    'email_low_inventory_alerts' => true,
                    'email_pending_payments' => true,
                    'email_bookings' => true,
                    'email_user_signups' => false,
                    'theme_preference' => 'light',
                    'language' => 'en',
                ]);
            }
        });
    }

    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getPhoneAttribute()
    {
        return $this->mobile_number;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function adminPreference()
    {
        return $this->hasOne(AdminPreference::class);
    }

    public function getOrCreateAdminPreference()
    {
        return $this->adminPreference ?? $this->adminPreference()->create([
            'email_low_inventory_alerts' => true,
            'email_pending_payments' => true,
            'email_bookings' => true,
            'email_user_signups' => false,
            'theme_preference' => 'light',
            'language' => 'en',
        ]);
    }

    public function adminActivityLogs()
    {
        return $this->hasMany(AdminActivityLog::class);
    }

    public function customerAddresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }
}
