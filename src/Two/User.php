<?php namespace Laravel\Socialite\Two;

use Laravel\Socialite\AbstractUser;

class User extends AbstractUser
{

    /**
     * The user's access token.
     *
     * @var string
     */
    public $token;

    /**
     * The user's refresh token.
     *
     * @var string
     */
    public $refreshToken;

    /**
     * The user's access token expiration delay.
     *
     * @var int
     */
    public $tokenExpiresIn;

    /**
     * Set the token on the user.
     *
     * @param  string  $token
     * @return $this
     */
    public function setToken($token, $refreshToken, $tokenExpiresIn)
    {
        $this->token = $token;
        $this->refreshToken = $refreshToken;
        $this->tokenExpiresIn = $tokenExpiresIn;

        return $this;
    }
}
