<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandLinks extends Model
{
    use HasFactory;

    public $table = 'brand_links';

    public $fillable = [
        'website',
        'email',
        'social',
        'user_id'
    ];
}