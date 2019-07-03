<?php

namespace App\Log;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Log\Contracts\LoggableContract;

trait Loggable
{
    protected $excludedAttributes = [];

    protected $logEvent;

    public static $logDisabled = false;

    /**
     * Boot method of model
     */
    public static function bootLoggable()
    {
        if (!static::$logDisabled) {
            static::observe(new LogObserver());
        }
    }

    /**
     * Each model has a relation to Log::class Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function log(): MorphMany
    {
        return $this->morphMany(Log::class, 'loggable');
    }

    /**
     * Method to disable log
     */
    public function disableLog()
    {
        static::$logDisabled = true;
    }

    /**
     * Method to enable log
     */
    public static function enableLog()
    {
        static::$logDisabled = false;
    }

    /**
     * Method which fires when create a model
     *
     * @return array
     */
    protected function getCreatedEventAttributes(): array
    {
        $new = [];

        foreach ($this->attributes as $attribute => $value) {
            if ($this->isAttributeLoggable($attribute)) {
                $new[$attribute] = $value;
            }
        }
        return [
            [],
            $new,
        ];
    }

    /**
     * Method which fires when update a model
     *
     * @return array
     */
    protected function getUpdatedEventAttributes(): array
    {
        $old = [];
        $new = [];

        foreach ($this->getDirty() as $attribute => $value) {
            if ($this->isAttributeLoggable($attribute)) {
                $old[$attribute] = Arr::get($this->original, $attribute);
                $new[$attribute] = Arr::get($this->attributes, $attribute);
            }
        }

        return [
            $old,
            $new,
        ];
    }

    /**
     * @return array
     */
    protected function getDeletedEventAttributes(): array
    {
        $old = [];

        foreach ($this->attributes as $attribute => $value) {
            if ($this->isAttributeLoggable($attribute)) {
                $old[$attribute] = $value;
            }
        }

        return [
            $old,
            [],
        ];
    }

    /**
     * @return array
     */
    protected function getRetrievedEventAttributes(): array
    {
        return [
            [],
            [],
        ];
    }

    /**
     * $logInclude is an array which is defined in models and it includes the columns which we want to be recorded.
     *
     * @return array
     */
    public function getLogInclude(): array
    {
        return $this->logInclude ?? [];
    }

    /**
     * $logExclude is an array which is defined in models and it excludes the columns which we dont want to be recorded.
     *
     * @return array
     */
    public function getLogExclude(): array
    {
        return $this->logExclude ?? [];
    }

    /**
     * Check if an attribute is in $logInclude array or if not defined $logInclude array
     *
     * @param  string  $attribute
     *
     * @return bool
     */
    protected function isAttributeLoggable(string $attribute): bool
    {
        if (in_array($attribute, $this->excludedAttributes, true)) {
            return false;
        }

        $include = $this->getLogInclude();

        return empty($include) || in_array($attribute, $include, true);
    }

    /**
     * Get all the log events
     *
     * @return array
     */
    public function getLogEvents(): array
    {
        return [
            'created',
            'updated',
            'deleted',
            'retrieved',
        ];
    }

    /**
     * @param  string  $event
     *
     * @return \App\Log\Contracts\LoggableContract
     */
    public function setLogEvent(string $event): LoggableContract
    {
        $this->logEvent = $this->isEventLoggable($event) ? $event : null;

        return $this;
    }

    /**
     * Check if method exists for the event getCreatedEventAttributes
     *
     * @param $event
     *
     * @return bool
     */
    protected function isEventLoggable($event): bool
    {
        return is_string($this->resolveAttributeGetter($event));
    }

    /**
     * It returns the method of the event e.x. getCreatedEventAttributes
     *
     * @param $event
     *
     * @return string
     */
    protected function resolveAttributeGetter($event)
    {
        foreach ($this->getLogEvents() as $key => $value) {
            $logEvent = is_int($key) ? $value : $key;

            $logEventRegex = sprintf('/%s/', preg_replace('/\*+/', '.*', $logEvent));

            if (preg_match($logEventRegex, $event)) {
                return is_int($key) ? sprintf('get%sEventAttributes', ucfirst($event)) : $value;
            }
        }
    }

    /**
     * @return string
     */
    public function getLogDriver()
    {
        return $this->logDriver ?? 'database';
    }

    public function toLog(): array
    {
        $attributeGetter = $this->resolveAttributeGetter($this->logEvent);

        if (!method_exists($this, $attributeGetter)) {
            dd(sprintf(
                'Unable to handle "%s" event, %s() method missing',
                $this->logEvent,
                $attributeGetter
            ));
        }

        list($old, $new) = $this->$attributeGetter();

        return $this->transformLog([
            'old_values' => $old,
            'new_values' => $new,
            'event' => $this->logEvent,
            'logable_id' => $this->getKey(),
            'logable_type' => $this->getMorphClass(),
            'user_id' => 1, // Auth::id(),
        ]);
    }

    public function transformLog(array $data): array
    {
        return $data;
    }
}