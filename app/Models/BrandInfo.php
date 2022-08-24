<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandInfo extends Model
{
    use HasFactory;

    public $table = 'brand_info';

    public $fillable = [
        'key',
        'txt',
        'user_id'
    ];
}
