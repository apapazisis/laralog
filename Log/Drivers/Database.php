<?php

namespace App\Log\Drivers;

use App\Log\Contracts\LogDriverContract;
use App\Log\Contracts\LogContract;
use App\Log\Contracts\LoggableContract;
use App\Log\Models\Log;

class Database implements LogDriverContract
{
    public function log(LoggableContract $model): LogContract
    {
        return call_user_func([Log::class, 'create'], $model->toLog());
    }

    public function prune(LoggableContract $model): bool
    {
        return false;
    }
}