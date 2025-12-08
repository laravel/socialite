<?php

namespace Laravel\Socialite\Testing;

use Closure;
use Illuminate\Support\Testing\Fakes\Fake;
use InvalidArgumentException;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User;
use PHPUnit\Framework\Assert as PHPUnit;

class SocialiteFake implements Factory, Fake
{
    /**
     * The original factory instance.
     *
     * @var \Laravel\Socialite\Contracts\Factory
     */
    protected $factory;

    /**
     * The fake user to return for each driver.
     *
     * @var array<string, \Laravel\Socialite\Contracts\User|\Closure>
     */
    protected array $users = [];

    /**
     * The fake provider instances.
     *
     * @var array<string, \Laravel\Socialite\Testing\FakeProvider>
     */
    protected array $providers = [];

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
     * @param  \Laravel\Socialite\Contracts\User|\Closure|null  $user
     * @return $this
     */
    public function fake($driver, $user = null)
    {
        $resolver = fn () => $this->factory->driver($driver);

        $this->providers[$driver] = new FakeProvider($driver, $resolver, $user);

        return $this;
    }
}

