<?php

namespace Laravel\Socialite\Two;

class PinterestProvider extends AbstractProvider implements ProviderInterface
{
    protected $fields = [
        'id', 'username', 'url', 'first_name', 'last_name', 'bio', 'image',
    ];

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://api.pinterest.com/oauth/', $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return 'https://api.pinterest.com/v1/oauth/token';
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param  string $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $url = 'https://api.pinterest.com/v1/me';

        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'x-li-format' => 'json',
            ],
            'query' => [
                'access_token' => $token,
                'fields' => implode(',', $this->fields),
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array $user
     * @return \Laravel\Socialite\User
     */
    protected function mapUserToObject(array $user)
    {
        $user = $user['data'];

        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => $user['username'],
            'name' => $user['first_name'].' '.$user['last_name'],
            'email' => null,
            'avatar' => $user['image']['60x60']['url'],
            'avatar_original' => null,
        ]);
    }
}
