<?php

namespace Laravel\Socialite;

use Illuminate\Support\Facades\Facade;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Testing\SocialiteFake;

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

    /**
     * Register a fake Socialite instance.
     *
     * @param  string  $driver
     * @param  \Laravel\Socialite\Contracts\User|\Closure|null  $user
     * @param  \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse|null  $redirect
     * @return \Laravel\Socialite\Testing\SocialiteFake
     */
    public static function fake($driver, $user = null, $redirect = null)
    {
        $fake = static::isFake()
            ? static::getFacadeRoot()
            : tap(new SocialiteFake(static::getFacadeRoot()), fn ($fake) => static::swap($fake));

        return $fake->fake($driver, $user, $redirect);
    }
}
