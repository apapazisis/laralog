<?php

namespace App\Log\Contracts;

interface LogContract
{
    public function getMetadata(bool $json = false, int $options = 0, int $depth = 512);

    public function resolveData(): array;
}