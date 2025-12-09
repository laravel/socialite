<?php

namespace Laravel\Socialite\Testing;

use Laravel\Socialite\Contracts\Factory;

class SocialiteFake implements Factory
{
    /**
     * The original factory instance.
     *
     * @var \Laravel\Socialite\Contracts\Factory
     */
    protected $factory;

    /**
     * The fake provider instances.
     *
     * @var array<string, \Laravel\Socialite\Testing\FakeProvider>
     */
    protected $providers = [];

    /**
     * Create a new Socialite fake instance.
     *
     * @param  \Laravel\Socialite\Contracts\Factory  $factory
     */
    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    /**
     * Get an OAuth provider implementation.
     *
     * @param  string  $driver
     * @return \Laravel\Socialite\Contracts\Provider
     */
    public function driver($driver = null)
    {
        return $this->providers[$driver] ?? $this->factory->driver($driver);
    }

    /**
     * Register a fake user for the given driver.
     *
     * @param  string  $driver
     * @param  \Laravel\Socialite\Contracts\User|\Closure|array|null  $user
     * @return $this
     */
    public function fake($driver, $user = null)
    {
        $resolver = function () use ($driver) {
            return $this->factory->driver($driver);
        };

        $this->providers[$driver] = new FakeProvider($driver, $resolver, $user);

        return $this;
    }
}
