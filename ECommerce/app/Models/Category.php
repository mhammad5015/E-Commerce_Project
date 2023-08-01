<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use NodeTrait;

    protected $table = 'categories';
    protected $fillable = ['name', 'image'];
    public $timestamps = false;

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'products');
    }
}
