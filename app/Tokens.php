<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tokens extends Model
{
    protected $table = 'token';

    protected $fillable = [
        'user_id', 'token', 'device_id', 'os'
    ];
}
