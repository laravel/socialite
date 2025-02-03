<?php

namespace Laravel\Socialite\Facades;

use Illuminate\Support\Facades\Facade;
use Laravel\Socialite\Contracts\Factory;

/**
 * @method static \Laravel\Socialite\Contracts\Provider driver(string $driver = null)
 * @method static \Laravel\Socialite\Two\AbstractProvider buildProvider(string $provider, array $config)
 * @method static \Laravel\Socialite\SocialiteManager extend(string $driver, \Closure $callback)
 * @method array getScopes()
 * @method \Laravel\Socialite\Contracts\Provider scopes(array|string $scopes)
 * @method \Laravel\Socialite\Contracts\Provider setScopes(array|string $scopes)
 * @method \Laravel\Socialite\Contracts\Provider redirectUrl(string $url)
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
