<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingCost extends Model
{
    use HasFactory;

    public $table = 'shipping_cost';

    public $fillable = [
        'user_id',
        'product_id',
        'ship_from_quantity',
        'ship_to_quantity',
        'ship_cost',
    ];
}