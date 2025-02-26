<?php

namespace Laravel\Socialite\Exceptions;

use InvalidArgumentException;

class DriverMissingConfigurationException extends InvalidArgumentException
{
    /**
     * Create a new exception for a missing configuration.
     *
     * @param  string  $driver
     * @param  array<int, string>  $keys
     * @return static
     */
    public static function missingConfig($driver, $keys)
    {
        return new static("Missing required configuration keys [" . implode(', ', $keys) . "] for [{$driver}] OAuth provider.");
    }
} 