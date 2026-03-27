<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PendingUser extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'username',
        'password',
        'token',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-hash password if it's being set and isn't already hashed
        static::saving(function ($user) {
            if ($user->isDirty('password')) {
                $password = $user->password;
                // Only hash if it doesn't look like a bcrypt hash
                if ($password && !preg_match('/^\$2[aby]\$/', $password)) {
                    $user->password = Hash::make($password);
                }
            }
        });
    }
}
