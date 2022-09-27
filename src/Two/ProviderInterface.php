<?php

namespace Laravel\Socialite\Two;

interface ProviderInterface
{
    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect(): \Symfony\Component\HttpFoundation\RedirectResponse;

    /**
     * Get the User instance for the authenticated user.
     *
     * @return \Laravel\Socialite\Two\User
     */
    public function user(): \Laravel\Socialite\Two\User;
}
