<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentOrders extends Model
{
    use HasFactory;

    public $table = 'payment_orders';
}