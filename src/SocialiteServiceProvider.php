<?php namespace Laravel\Socialite;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\FacebookProvider;
use Laravel\Socialite\One\TwitterProvider;
use League\OAuth1\Client\Server\Twitter as TwitterServer;

class SocialiteServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('Laravel\Socialite\Contracts\Factory', function($app)
		{
			return new SocialiteManager($app);
		});

		$this->app->extend('Laravel\Socialite\Contracts\Factory', function($socialite, $app)
		{
			$config = $app['config'];

			$this->registerOAuthOneProviders($socialite, $config);
			$this->registerOAuthTwoProviders($socialite, $config);

			return $socialite;
		});
	}

	/**
	 * Registers all OAuth One providers.
	 *
	 * @param  \Laravel\Socialite\Contracts\Factory $socialite
	 * @param  \Illuminate\Contracts\Config\Repository $config
	 * @return void
	 */
	public function registerOAuthOneProviders($socialite, $config)
	{
		$socialite->extend('twitter', function($app) use($config)
		{
			$config = $config['services.twitter'];

			return new TwitterProvider($app['request'],
				new TwitterServer([
					'identifier'   => $config['client_id'],
					'secret'       => $config['client_secret'],
					'callback_url' => $config['redirect'],
				])
			);
		});
	}

	/**
	 * Registers all OAuth Two providers.
	 *
	 * @param  \Laravel\Socialite\Contracts\Factory $socialite
	 * @param  \Illuminate\Contracts\Config\Repository $config
	 * @return void
	 */
	public function registerOAuthTwoProviders($socialite, $config)
	{
		$socialite->extend('github', function($app) use($socialite, $config)
		{
			$config = $config['services.github'];

			return new GithubProvider(
				$app['request'], $config['client_id'],
				$config['client_secret'], $config['redirect']
			);
		});

		$socialite->extend('google', function($app) use($socialite, $config)
		{
			$config = $config['services.google'];

			return new GoogleProvider(
				$app['request'], $config['client_id'],
				$config['client_secret'], $config['redirect']
			);
		});

		$socialite->extend('facebook', function($app) use($socialite, $config)
		{
			$config = $config['services.facebook'];

			return new FacebookProvider(
				$app['request'], $config['client_id'],
				$config['client_secret'], $config['redirect']
			);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['Laravel\Socialite\Contracts\Factory'];
	}

}
