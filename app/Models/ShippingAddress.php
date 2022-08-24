<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    use HasFactory;

    public $table = 'shipping_address';

    public $fillable = [
        'buyer_id',
        'address',
        'phone',
        'email',
    ];
}