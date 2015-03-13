<?php namespace Laravel\Socialite\Contracts;

interface Provider
{

    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @param bool $rerequest Whether or not to rerequest permissions.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect($rerequest = false);

    /**
     * Get the User instance for the authenticated user.
     *
     * @return \Laravel\Socialite\Contracts\User
     */
    public function user();
}
