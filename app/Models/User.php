<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // A user can handle many transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function setPasswordAttribute($value)
    {
    $this->attributes['password'] = bcrypt($value);
    }

}
