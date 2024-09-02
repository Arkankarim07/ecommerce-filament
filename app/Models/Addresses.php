<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    // menggabungkan first_name dan last_name
    public function getFullNameAttribute() {
        return $this->first_name . ' ' . $this->last_name;
    }
}
