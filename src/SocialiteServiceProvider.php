<?php namespace Laravel\Socialite;

use Illuminate\Support\ServiceProvider;

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
		$this->app->bindShared('Illuminate\Contracts\Auth\Social\Factory', function($app)
		{
			return new SocialiteManager($app);
		});

		$this->app->bindShared('Illuminate\Contracts\Auth\Social\Provider', function($app)
		{
			return $app['Illuminate\Contracts\Auth\Social\Factory']->driver();
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['Illuminate\Contracts\Auth\Social\Factory', 'Illuminate\Contracts\Auth\Social\Provider'];
	}

}
