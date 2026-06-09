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
    public $token;

    /**
     * The user's access token secret.
     *
     * @var string
     */
    public $tokenSecret;

    /**
     * Create a fake OAuth 1 user instance.
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
            'tokenSecret' => 'fake-token-secret',
        ], $attributes);

        return (new self)->setRaw($attributes)->map($attributes)
            ->setToken($attributes['token'], $attributes['tokenSecret']);
    }

    /**
     * Set the token on the user.
     *
     * @param  string  $token
     * @param  string  $tokenSecret
     * @return $this
     */
    public function setToken($token, $tokenSecret)
    {
        $this->token = $token;
        $this->tokenSecret = $tokenSecret;

        return $this;
    }
}
