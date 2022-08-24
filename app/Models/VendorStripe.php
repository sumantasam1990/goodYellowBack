<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorStripe extends Model
{
    use HasFactory;

    public $table = 'vendor_stripe';

    public $fillable = [
        'user_id',
        'api_key',
        'secret_key'
    ];
}
