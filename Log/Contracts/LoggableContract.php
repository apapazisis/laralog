<?php

namespace App\Log\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface LoggableContract
{
    public function log(): MorphMany;

    public function setLogEvent(string $event): LoggableContract;

    public function getLogEvents(): array;

    public function getLogInclude(): array;

    public function getLogExclude(): array;

    public function getLogDriver(): string;

    public function toLog(): array;

    public function readyForLogging(): bool;
}