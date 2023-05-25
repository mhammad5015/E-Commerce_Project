<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'admins';
    //protected $primaryKey = "admin_id";
    protected $fillable = [
        'company_name', 'email', 'password', 'logo', 'phone_number',
        'wallet',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public $timestamps = false;

    // get the addresses
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
