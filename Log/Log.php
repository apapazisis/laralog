<?php

namespace App\Log;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait Log
{
    protected $data = [];

    protected $metadata = [];

    protected $modified = [];

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            Config::get('log.user.model'),
            Config::get('log.user.foreign_key'),
            Config::get('log.user.primary_key')
            );
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

    public function getModified(bool $json = false, int $options = 0, int $depth = 512)
    {
        if (empty($this->data)) {
            $this->resolveData();
        }

        $modified = [];

        foreach ($this->modified as $key) {
            $attribute = substr($key, 4);
            $state = substr($key, 0, 3);

            $value = $this->getDataValue($key);

            $modified[$attribute][$state] = $value instanceof DateTimeInterface
                ? $this->serializeDate($value)
                : $value;
        }

        return $json ? json_encode($modified, $options, $depth) : $modified;
    }

    /**
     * @return array
     */
    public function resolveData(): array
    {
        $this->data = [
            'log_id' => $this->id,
            'log_event' => $this->event,
            'log_created_at' => $this->serializeDate($this->created_at),
            'log_updated_at' => $this->serializeDate($this->updated_at),
            'user_id' => $this->getAttribute(Config::get('log.user.foreign_key', 'user_id')),
        ];

        // $this->metadata has as values the keys of $this->data
        $this->metadata = array_keys($this->data);

        foreach ($this->new_values as $key => $value) {
            $this->data['new_'.$key] = $value;
        }

        foreach ($this->old_values as $key => $value) {
            $this->data['old_'.$key] = $value;
        }

        // It keeps only the new_ and old_ values e.x. new_name, old_name
        $this->modified = array_diff_key(array_keys($this->data), $this->metadata);

        return $this->data;
    }

    public function getDataValue(string $key)
    {
        if (!array_key_exists($key, $this->data)) {
            return;
        }

        $value = $this->data[$key];

        if ($this->user && Str::startsWith($key, 'user_')) {
            return $this->getFormattedValue($this->user, substr($key, 5), $value);
        }

        if ($this->loggable && Str::startsWith($key, ['new_', 'old_'])) {
            $attribute = substr($key, 4);

            return $this->getFormattedValue(
                $this->loggable,
                $attribute,
                $value
            );
        }

        return $value;
    }

    protected function getFormattedValue(Model $model, string $key, $value)
    {
        if ($model->hasGetMutator($key)) {
            return $model->mutateAttribute($key, $value);
        }

        if ($model->hasCast($key)) {
            return $model->castAttribute($key, $value);
        }

        if ($value !== null && in_array($key, $model->getDates(), true)) {
            return $model->asDateTime($value);
        }

        return $value;
    }
}