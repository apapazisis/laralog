<?php

namespace App\Log\Models;

use App\Log\Contracts\LogContract;
use Illuminate\Database\Eloquent\Model;

class Log extends Model implements LogContract
{
    use \App\Log\Log;

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'logable_id' => 'integer',
    ];
}