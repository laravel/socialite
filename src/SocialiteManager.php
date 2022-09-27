<?php

namespace Laravel\Socialite;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Socialite\One\TwitterProvider;
use Laravel\Socialite\Two\BitbucketProvider;
use Laravel\Socialite\Two\FacebookProvider;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\GitlabProvider;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\LinkedInProvider;
use Laravel\Socialite\Two\TwitterProvider as TwitterOAuth2Provider;
use League\OAuth1\Client\Server\Twitter as TwitterServer;

class SocialiteManager extends Manager implements Contracts\Factory
{
    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function with(string $driver): mixed
    {
        return $this->driver($driver);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createGithubDriver(): \Laravel\Socialite\Two\AbstractProvider
    {
        $config = $this->config->get('services.github');

        return $this->buildProvider(
            GithubProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createFacebookDriver(): \Laravel\Socialite\Two\AbstractProvider
    {
        $config = $this->config->get('services.facebook');

        return $this->buildProvider(
            FacebookProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createGoogleDriver(): \Laravel\Socialite\Two\AbstractProvider
    {
        $config = $this->config->get('services.google');

        return $this->buildProvider(
            GoogleProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createLinkedinDriver(): \Laravel\Socialite\Two\AbstractProvider
    {
        $config = $this->config->get('services.linkedin');

        return $this->buildProvider(
          LinkedInProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createBitbucketDriver(): \Laravel\Socialite\Two\AbstractProvider
    {
        $config = $this->config->get('services.bitbucket');

        return $this->buildProvider(
          BitbucketProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createGitlabDriver(): \Laravel\Socialite\Two\AbstractProvider
    {
        $config = $this->config->get('services.gitlab');

        return $this->buildProvider(
            GitlabProvider::class, $config
        )->setHost($config['host'] ?? null);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createTwitterOAuth2Driver(): \Laravel\Socialite\Two\AbstractProvider
    {
        $config = $this->config->get('services.twitter');

        return $this->buildProvider(
            TwitterOAuth2Provider::class, $config
        );
    }

    /**
     * Build an OAuth 2 provider instance.
     *
     * @param string $provider
     * @param array $config
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    public function buildProvider(string $provider, array $config): \Laravel\Socialite\Two\AbstractProvider
    {
        return new $provider(
            $this->container->make('request'), $config['client_id'],
            $config['client_secret'], $this->formatRedirectUrl($config),
            Arr::get($config, 'guzzle', [])
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\One\AbstractProvider
     */
    protected function createTwitterDriver(): \Laravel\Socialite\One\AbstractProvider
    {
        $config = $this->config->get('services.twitter');

        return new TwitterProvider(
            $this->container->make('request'), new TwitterServer($this->formatConfig($config))
        );
    }

    /**
     * Format the server configuration.
     *
     * @param array $config
     * @return array
     */
    public function formatConfig(array $config): array
    {
        return array_merge([
            'identifier' => $config['client_id'],
            'secret' => $config['client_secret'],
            'callback_uri' => $this->formatRedirectUrl($config),
        ], $config);
    }

    /**
     * Format the callback URL, resolving a relative URI if needed.
     *
     * @param array $config
     * @return string
     */
    protected function formatRedirectUrl(array $config): string
    {
        $redirect = value($config['redirect']);

        return Str::startsWith($redirect, '/')
                    ? $this->container->make('url')->to($redirect)
                    : $redirect;
    }

    /**
     * Forget all of the resolved driver instances.
     *
     * @return $this
     */
    public function forgetDrivers(): self
    {
        $this->drivers = [];

        return $this;
    }

    /**
     * Set the container instance used by the manager.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return $this
     */
    public function setContainer(\Illuminate\Contracts\Container\Container $container): self
    {
        $this->app = $container;
        $this->container = $container;

        return $this;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getDefaultDriver(): string
    {
        throw new InvalidArgumentException('No Socialite driver was specified.');
    }
}
