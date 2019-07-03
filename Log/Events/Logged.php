<?php

namespace App\Log\Events;

use App\Log\Contracts\LogDriverContract;
use App\Log\Contracts\LoggableContract;

class Logged
{
    public $model;

    public $driver;

    public $log;

    public function __construct(LoggableContract $model, LogDriverContract $driver, $log = null)
    {
        $this->model = $model;
        $this->driver = $driver;
        $this->log = $log;
    }
}
