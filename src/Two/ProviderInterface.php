<?php

namespace Laravel\Socialite\Two;

interface ProviderInterface
{
    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect();

    /**
     * Get the User instance for the authenticated user.
     *
     * @return \Laravel\Socialite\Two\User
     */
    public function user();
}
