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
        'company_name', 'email', 'password', 'logo', 'Commercial_Record', 'phone_number',
        'wallet','percentage',
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


    public function categoriesWithProducts()
    {
        $admin_id = $this->id;
        return $this->belongsToMany(Category::class, 'products')
            //  ->with('ancestors')
            ->with(['products' => function ($query) use ($admin_id) {
                $query->where('admin_id', $admin_id);
            }])
            ->wherePivot('admin_id', $this->id);
    }


    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'products');
    }

    public function adds()
    {
        return $this->hasMany(Ad::class, 'admin_id');
    }
}
