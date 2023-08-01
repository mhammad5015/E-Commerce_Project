<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant_cart extends Model
{
    use HasFactory;
    protected $fillable = ['cart_id','variant_id','quantity'];
    public $timestamps = false;
    
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
