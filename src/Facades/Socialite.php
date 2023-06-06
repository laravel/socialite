<?php

namespace Laravel\Socialite\Facades;

use Illuminate\Support\Facades\Facade;
use Laravel\Socialite\Contracts\Factory;

/**
 * @method static \Laravel\Socialite\Contracts\Provider driver(string $driver = null)
 * @method static \Laravel\Socialite\Two\AbstractProvider buildProvider($provider, $config)
 *
 * @see \Laravel\Socialite\SocialiteManager
 */
class Socialite extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
