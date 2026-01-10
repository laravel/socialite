<?php

namespace Laravel\Socialite\Testing;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Traits\ForwardsCalls;
use Laravel\Socialite\Contracts\Provider;

class FakeProvider implements Provider
{
    use ForwardsCalls;

    /**
     * The driver name.
     *
     * @var string
     */
    protected $driver;

    /**
     * The provider resolver.
     *
     * @var \Closure
     */
    protected $resolver;

    /**
     * The original provider instance.
     *
     * @var \Laravel\Socialite\Contracts\Provider
     */
    protected $provider;

    /**
     * The fake user to return.
     *
     * @var \Laravel\Socialite\Contracts\User|\Closure|array|null
     */
    protected $user = null;

    /**
     * Create a new fake provider instance.
     *
     * @param  string  $driver
     * @param  \Closure  $resolver
     * @param  \Laravel\Socialite\Contracts\User|\Closure|array|null  $user
     */
    public function __construct($driver, $resolver, $user = null)
    {
        $this->driver = $driver;
        $this->resolver = $resolver;
        $this->user = $user;
    }

    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        return new RedirectResponse('https://socialite.fake/'.$this->driver.'/authorize');
    }

    /**
     * Get the User instance for the authenticated user.
     *
     * @return \Laravel\Socialite\Contracts\User
     */
    public function user()
    {
        if ($this->user instanceof Closure) {
            return ($this->user)();
        }

        return $this->user ?? $this->provider()->user();
    }

    /**
     * Get the original provider instance.
     *
     * @return \Laravel\Socialite\Contracts\Provider
     */
    public function provider()
    {
        if (isset($this->provider)) {
            return $this->provider;
        }

        return $this->provider = ($this->resolver)();
    }

    /**
     * Handle calls to methods that are not available on the fake provider.
     *
     * @param  string  $method
     */
    public function __call($method, array $parameters)
    {
        return $this->forwardDecoratedCallTo($this->provider(), $method, $parameters);
    }
}
