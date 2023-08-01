<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    //protected $fillable = ['name', 'price', 'description', 'have_discount', 'approved', 'product_quantity'];

    protected $fillable = ['admin_id', 'category_id', 'name', 'price', 'description', 'have_discount', 'approved', 'product_quantity'];
    public $timestamps = false;

    public function pendingProduct()
    {
        return $this->hasOne(PendingProduct::class);
    }

    public function productImages()
    {
        return $this->hasMany(Product_image::class);
    }
    public function productTags()
    {
        return $this->hasMany(Product_tag::class);
    }
    public function productVariants()
    {
        return $this->hasMany(Variant::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function favorite()
    {
        return $this->hasMany(Favorite::class);
    }
    public function comment()
    {
        return $this->hasMany(Comment::class);
    }
}
