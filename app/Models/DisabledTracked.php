<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisabledTracked extends Model
{
    protected $fillable = [
        'timestamp',
    ];

    protected $table = 'disabled_tracked';
}
