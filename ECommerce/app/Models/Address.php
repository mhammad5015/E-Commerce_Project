<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $fillable = ['address'];
    protected $table = 'addresses';
    public $timestamps = false;
    
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
