<?php

namespace Laravel\Socialite;

use InvalidArgumentException;
use Illuminate\Support\Manager;

class SocialiteManager extends Manager implements Contracts\Factory
{
    /**
     * Supported providers.
     *
     * @var array
     */
    private $providers = [

        /*
        |--------------------------------------------------------------------------
        | OAuth2
        |--------------------------------------------------------------------------
        */
        'github'   => \Laravel\Socialite\Two\GithubProvider::class,
        'facebook' => \Laravel\Socialite\Two\FacebookProvider::class,
        'google'   => \Laravel\Socialite\Two\GoogleProvider::class,
        'linkedin' => \Laravel\Socialite\Two\LinkedInProvider::class,

        /*
        |--------------------------------------------------------------------------
        | OAuth1
        |--------------------------------------------------------------------------
        */
        'twitter' => [
            'provider' => \Laravel\Socialite\One\TwitterProvider::class,
            'server'   => \League\OAuth1\Client\Server\Twitter::class,
        ],
        'bitbucket' => [
            'provider' => \Laravel\Socialite\One\BitbucketProvider::class,
            'server'   => \League\OAuth1\Client\Server\Bitbucket::class,
        ],
    ];

    /**
     * Extend/override supported providers.
     *
     * @param  array  $providers
     * @return self
     */
    public function extendProviders($providers)
    {
        $this->providers = array_merge($this->providers, $providers);
        return $this;
    }

    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function with($driver)
    {
        return $this->driver($driver);
    }

    /**
     * Get a dynamic driver instance.
     *
     * @param  string  $driver
     * @param  array   $config
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function withDynamic($driver, $config)
    {
        if (! isset($this->providers[$driver])) {
            throw new InvalidArgumentException("Driver [$driver] not supported.");
        }

        $provider = $this->providers[$driver];

        if (is_array($provider)) {
            return new $provider['provider'](
                $this->app['request'], new $provider['server']($this->formatConfig($config))
            );
        } else {
            return $this->buildProvider(
                $provider, $config
            );
        }
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createGithubDriver()
    {
        $config = $this->app['config']['services.github'];

        return $this->buildProvider(
            $this->providers['github'], $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createFacebookDriver()
    {
        $config = $this->app['config']['services.facebook'];

        return $this->buildProvider(
            $this->providers['facebook'], $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createGoogleDriver()
    {
        $config = $this->app['config']['services.google'];

        return $this->buildProvider(
            $this->providers['google'], $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createLinkedinDriver()
    {
        $config = $this->app['config']['services.linkedin'];

        return $this->buildProvider(
          $this->providers['linkedin'], $config
        );
    }

    /**
     * Build an OAuth 2 provider instance.
     *
     * @param  string  $provider
     * @param  array  $config
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    public function buildProvider($provider, $config)
    {
        return new $provider(
            $this->app['request'], $config['client_id'],
            $config['client_secret'], $config['redirect']
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\One\AbstractProvider
     */
    protected function createTwitterDriver()
    {
        $config = $this->app['config']['services.twitter'];

        return new $this->providers['twitter']['provider'](
            $this->app['request'], new $this->providers['twitter']['server']($this->formatConfig($config))
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\One\AbstractProvider
     */
    protected function createBitbucketDriver()
    {
        $config = $this->app['config']['services.bitbucket'];

        return new $this->providers['bitbucket']['provider'](
            $this->app['request'], new $this->providers['bitbucket']['server']($this->formatConfig($config))
        );
    }

    /**
     * Format the server configuration.
     *
     * @param  array  $config
     * @return array
     */
    public function formatConfig(array $config)
    {
        return array_merge([
            'identifier' => $config['client_id'],
            'secret' => $config['client_secret'],
            'callback_uri' => $config['redirect'],
        ], $config);
    }

    /**
     * Get the default driver name.
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        throw new InvalidArgumentException('No Socialite driver was specified.');
    }
}
