<?php namespace Laravel\Socialite;

use Illuminate\Support\Manager;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\One\TwitterProvider;
use Laravel\Socialite\Two\FacebookProvider;
use League\OAuth1\Client\Server\Twitter as TwitterServer;
use Laravel\Socialite\One\AbstractProvider as AbstractOneProvider;
use Laravel\Socialite\Two\AbstractProvider as AbstractTwoProvider;

class SocialiteManager extends Manager implements Contracts\Factory {

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
	 * Create an instance of the specified driver.
	 *
	 * @return \Laravel\Socialite\Two\AbstractProvider
	 */
	protected function createGithubDriver()
	{
		$config = $this->app['config']['services.github'];

		return $this->buildProvider(
			'Laravel\Socialite\Two\GithubProvider', $config
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
			'Laravel\Socialite\Two\FacebookProvider', $config
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
			'Laravel\Socialite\Two\GoogleProvider', $config
		);
	}

	/**
	 * Build an OAuth 2 provider instance.
	 *
	 * @param  string  $provider
	 * @param  array  $config
	 * @return \Laravel\Socialite\Two\AbstractProvider
	 */
	protected function buildProvider($provider, $config)
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

		return new TwitterProvider(
			$this->app['request'], new TwitterServer($this->formatConfig($config))
		);
	}

	/**
	 * Format the Twitter server configuration.
	 *
	 * @param  array  $config
	 * @return array
	 */
	protected function formatConfig(array $config)
	{
		return [
			'identifier' => $config['client_id'],
			'secret' => $config['client_secret'],
			'callback_uri' => $config['redirect'],
		];
	}

	/**
	 * Get the default driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		throw new \InvalidArgumentException("No Socialite driver was specified.");
	}

}
