<?php

namespace Laravel\Socialite\Contracts;

interface Provider
{
    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect();

    /**
     * Get the User instance for the authenticated user.
     *
     * @return \Laravel\Socialite\Contracts\User
     */

    public function user();
    /**
     * Get the User instance for the authenticated user by using access token.
     *
     * @param  string  $token
     * @return \Laravel\Socialite\Contracts\User
     */
    public function userFromToken($token);
}
