<?php

namespace App\Log\Contracts;

interface LogDriverContract
{
    public function log(LoggableContract $model): LogContract;

    public function prune(LoggableContract $model): bool;
}