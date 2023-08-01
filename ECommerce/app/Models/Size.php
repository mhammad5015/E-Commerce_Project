<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }
}
