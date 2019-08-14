<?php

namespace Laravel\Socialite\Facades;

use Illuminate\Support\Facades\Facade;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Contracts\Provider;

/**
 * @method static Provider driver(string $driver = null)
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
