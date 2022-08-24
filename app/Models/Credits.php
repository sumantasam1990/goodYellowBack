<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credits extends Model
{
    use HasFactory;

    public $table = 'subscription_credits';

    public $fillable = [
        'credits',
        'start_date',
        'end_date',
        'payment_buyer_id',
        'buyer_id'
    ];
}