<?php

namespace App\Log\Events;

use App\Log\Contracts\LogDriverContract;
use App\Log\Contracts\LoggableContract;

class Logging
{
    public $model;

    public $driver;

    public function __construct(LoggableContract $model, LogDriverContract $driver)
    {
        $this->model = $model;
        $this->driver = $driver;
    }
}