<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = ['user_id'];
    public $timestamps = false;
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function variant_cart()
    {
        return $this->hasMany(variant_cart::class);
    }
}
