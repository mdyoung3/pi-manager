<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedUrl extends Model
{
    protected $fillable = [
        'url',
    ];

    protected $table = 'blocked_urls';
}