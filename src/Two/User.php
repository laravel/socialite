<?php

namespace Laravel\Socialite\Two;

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
     * Create a fake OAuth 2 user instance.
     *
     * @param  array  $attributes
     * @return self
     */
    public static function fake(array $attributes = [])
    {
        $attributes = array_merge([
            'id' => '123456789',
            'nickname' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar' => 'https://example.com/avatar.jpg',
            'token' => 'fake-token',
            'refreshToken' => 'fake-refresh-token',
            'expiresIn' => 3600,
            'approvedScopes' => [],
        ], $attributes);

        return (new self)->setRaw($attributes)->map($attributes)
            ->setToken($attributes['token'])
            ->setRefreshToken($attributes['refreshToken'])
            ->setExpiresIn($attributes['expiresIn'])
            ->setApprovedScopes($attributes['approvedScopes']);
    }

    /**
     * Set the token on the user.
     *
     * @param  string  $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Set the refresh token required to obtain a new access token.
     *
     * @param  string  $refreshToken
     * @return $this
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Set the number of seconds the access token is valid for.
     *
     * @param  int  $expiresIn
     * @return $this
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    /**
     * Set the scopes that were approved by the user during authentication.
     *
     * @param  array  $approvedScopes
     * @return $this
     */
    public function setApprovedScopes($approvedScopes)
    {
        $this->approvedScopes = $approvedScopes;

        return $this;
    }
}
