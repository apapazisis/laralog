<?php

namespace App\Log;

use App\Log\Contracts\LogDriverContract;
use App\Log\Drivers\Database;
use App\Log\Events\Logged;
use App\Log\Events\Logging;
use Illuminate\Support\Manager;
use App\Log\Contracts\LoggableContract;

class Logger extends Manager
{
    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return 'database';
    }

    /**
     * @param  \App\Log\Contracts\LoggableContract  $model
     *
     * @return mixed
     */
    public function logDriver(LoggableContract $model): LogDriverContract
    {
        return $this->driver($model->getLogDriver());
    }

    /**
     * @param  \App\Log\Contracts\LoggableContract  $model
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function execute(LoggableContract $model)
    {
        if (!$model->readyForLogging()) {
            return;
        }

        $driver = $this->logDriver($model);

        if (!$this->fireLogEvent($model, $driver)) {
            return;
        }

        if ($audit = $driver->log($model)) {
            $driver->prune($model);
        }

        $this->app->make('events')->dispatch(
            new Logged($model, $driver, $audit)
        );
    }

    /**
     * @return \App\Log\Drivers\Database
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createDatabaseDriver(): Database
    {
        return $this->app->make(Database::class);
    }

    /**
     * @param  \App\Log\Contracts\LoggableContract  $model
     * @param $driver
     *
     * @return bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function fireLogEvent(LoggableContract $model, LogDriverContract $driver): bool
    {
        return $this->app->make('events')->until(
                new Logging($model, $driver)
            ) !== false;
    }
}