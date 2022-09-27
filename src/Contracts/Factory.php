<?php

namespace Laravel\Socialite\Contracts;

interface Factory
{
    /**
     * Get an OAuth provider implementation.
     *
     * @param  string|null $driver
     * @return Provider
     */
    public function driver(string|null $driver = null): Provider;
}
