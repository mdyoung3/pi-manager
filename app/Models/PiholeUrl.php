<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiholeUrl extends Model
{
    protected $fillable = [
        'url',
    ];

    protected $table = 'pihole_urls';
}