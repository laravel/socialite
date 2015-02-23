<?php namespace Laravel\Socialite\Contracts;

use Closure;

interface Factory {

	/**
	 * Build an OAuth 2 provider instance.
	 *
	 * @param  string  $provider
	 * @param  array  $config
	 * @return \Laravel\Socialite\Two\AbstractProvider
	 */
	public function buildProvider($provider, $config);

	/**
	 * Get the default driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver();

	/**
	 * Get an OAuth provider implementation.
	 *
	 * @param  string  $driver
	 * @return \Laravel\Socialite\Contracts\Provider
	 */
	public function driver($driver = null);

	/**
	 * Register a custom driver creator Closure.
	 *
	 * @param  string	$driver
	 * @param  \Closure  $callback
	 * @return $this
	 */
	public function extend($driver, Closure $callback);

	/**
	 * Get all of the created "drivers".
	 *
	 * @return array
	 */
	public function getDrivers();

	/**
	 * Get a driver instance.
	 *
	 * @param  string  $driver
	 * @return mixed
	 */
	public function with($driver);

	/**
	 * Dynamically call the default driver instance.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters);

}
