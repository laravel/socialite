<?php

namespace Laravel\Socialite\Two;

class Token
{
    /**
     * The user's access token.
     *
     * @var string
     */
    public $token;

    /**
     * The refresh token that can be exchanged for a new access token.
     *
     * @var string
     */
    public $refreshToken;

    /**
     * The number of seconds the access token is valid for.
     *
     * @var int
     */
    public $expiresIn;

    /**
     * The scopes the user authorized. The approved scopes may be a subset of the requested scopes.
     *
     * @var array
     */
    public $approvedScopes;

    /**
     * Create a new token instance.
     *
     * @param  string  $token
     * @param  string  $refreshToken
     * @param  int  $expiresIn
     * @param  array  $approvedScopes
     */
    public function __construct(string $token, string $refreshToken, int $expiresIn, array $approvedScopes)
    {
        $this->token = $token;
        $this->refreshToken = $refreshToken;
        $this->expiresIn = $expiresIn;
        $this->approvedScopes = $approvedScopes;
    }
}
