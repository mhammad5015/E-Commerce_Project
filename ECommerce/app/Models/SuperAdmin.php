<?php

namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class SuperAdmin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
       'superadmin_name', 'email', 'password','profile_img_url','phone_number',
            'wallet',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    public $timestamps = false;
}
