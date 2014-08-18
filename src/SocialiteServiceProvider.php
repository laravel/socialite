<?php namespace Illuminate\Events;

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
		$this->app['socialite'] = $this->app->share(function($app)
		{
			return new SocialiteManager($app);
		});
	}

}
