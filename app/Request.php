<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = ['request'];

    protected $casts = [
        'request' => 'array',
    ];
}
