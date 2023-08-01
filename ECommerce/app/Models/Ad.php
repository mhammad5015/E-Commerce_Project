<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'admin_id', 'image',
    ];


    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
