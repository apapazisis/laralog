<?php

namespace App\Log;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait Log
{
    protected $data = [];

    protected $metadata = [];

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getMetadata(bool $json = false, int $options = 0, int $depth = 512)
    {
        if (empty($this->data)) {
            $this->resolveData();
        }

        $metadata = [];

        foreach ($this->metadata as $key) {
            $value = $this->getDataValue($key);

            $metadata[$key] = $value instanceof DateTimeInterface
                ? $this->serializeDate($value)
                : $value;
        }

        return $json ? json_encode($metadata, $options, $depth) : $metadata;
    }

    public function resolveData(): array
    {
        $this->data = [
            'audit_id' => $this->id,
            'audit_event' => $this->event,
            'audit_url' => $this->url,
            'audit_ip_address' => $this->ip_address,
            'audit_user_agent' => $this->user_agent,
            'audit_tags' => $this->tags,
            'audit_created_at' => $this->serializeDate($this->created_at),
            'audit_updated_at' => $this->serializeDate($this->updated_at),
            'user_id' => $this->getAttribute('user_id'),
            'user_type' => $this->getAttribute('user_type'),
        ];

        return $this->data;
    }
}