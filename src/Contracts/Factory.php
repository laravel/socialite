<?php

namespace Laravel\Socialite\Contracts;

interface Factory
{
    /**
     * Get an OAuth provider implementation.
     *
     * @param  string  $driver
     * @return \Laravel\Socialite\Contracts\Provider
     */
    public function driver($driver = null);

    /**
     * Configure driver.
     *
     * @param array $config
     * @return $this
     */
    public function configure(array $config);
}
