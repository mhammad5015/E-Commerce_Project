<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;
    protected $fillable = ['product_id','color_id','size_id','variant_quantity'];
    public $timestamps = false;


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function variantCarts()
    {
        return $this->hasMany(VariantCart::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }
}
