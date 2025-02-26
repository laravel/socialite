<?php

namespace Laravel\Socialite\Exceptions;

use InvalidArgumentException;

class DriverMissingConfigurationException extends InvalidArgumentException
{
    /**
     * Create a new exception for a missing configuration.
     *
     * @param  string  $provider
     * @param  array<int, string>  $keys
     * @return static
     */
    public static function make($provider, $keys)
    {
        return new static("Missing required configuration keys [" . implode(', ', $keys) . "] for [{$provider}] OAuth provider.");
    }
} 