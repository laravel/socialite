<?php

namespace Laravel\Socialite\One;

use Laravel\Socialite\AbstractUser;

class User extends AbstractUser
{
    /**
     * The user's access token.
     *
     * @var string
     */
    public string $token;

    /**
     * The user's access token secret.
     *
     * @var string
     */
    public string $tokenSecret;

    /**
     * Set the token on the user.
     *
     * @param  string  $token
     * @param  string  $tokenSecret
     * @return $this
     */
    public function setToken(string $token, string $tokenSecret): self
    {
        $this->token = $token;
        $this->tokenSecret = $tokenSecret;

        return $this;
    }
}
