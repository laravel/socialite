<?php namespace Laravel\Socialite\Two;

class Token
{

    /**
     * The access token.
     *
     * @var string
     */
    public $token;

    /**
     * The refresh token.
     *
     * @var string
     */
    public $refreshToken;

    /**
     * Set the token on the user.
     *
     * @param  string  $token
     * @param  string  $refreshToken
     * @return $this
     */
    public function __construct($token, $refreshToken = null)
    {
        $this->token = $token;
        $this->refreshToken = $refreshToken;

        return $this;
    }
}
