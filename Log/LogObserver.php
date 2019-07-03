<?php

namespace App\Log;

use App\Log\Contracts\LoggableContract;

class LogObserver
{
    public function created(LoggableContract $model)
    {
        (new Logger(app()))->execute($model->setLogEvent('created'));
    }

    public function updated(LoggableContract $model)
    {
        (new Logger(app()))->execute($model->setLogEvent('updated'));
    }

    public function retrieved(LoggableContract $model)
    {
        (new Logger(app()))->execute($model->setLogEvent('retrieved'));
    }

    public function deleted(LoggableContract $model)
    {
        (new Logger(app()))->execute($model->setLogEvent('deleted'));
    }
}