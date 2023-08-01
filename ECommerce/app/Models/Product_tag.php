<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_tag extends Model
{
    use HasFactory;

    public $timestamps = false;
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function variant()
    {
        return $this->hasMany(Variant::class);
    }
}
