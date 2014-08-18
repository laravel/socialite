<?php namespace Laravel\Socialite\One;

interface ProviderInterface {

	/**
	 * Redirect the user to the authentication page for the provider.
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function redirect();

	/**
	 * Get the User instance for the authenticated user.
	 *
	 * @return \Laravel\Socialite\One\User
	 */
	public function user();

}