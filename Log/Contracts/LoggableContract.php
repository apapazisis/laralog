<?php

namespace App\Log\Contracts;

interface LoggableContract
{
    public function setLogEvent(string $event): LoggableContract;

    public function getLogEvents(): array;

    public function getLogInclude(): array;

    public function getLogExclude(): array;

    public function getLogDriver();

    public function toLog(): array;
}