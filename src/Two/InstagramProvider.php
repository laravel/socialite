<?php

namespace Laravel\Socialite\Two;

class InstagramProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * Get the authentication URL for the provider.
     *
     * @param  string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://api.instagram.com/oauth/authorize/', $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return 'https://api.instagram.com/oauth/access_token?'.http_build_query([
            'grant_type' => 'authorization_code',
        ]);
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param  string $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $url = 'https://api.instagram.com/v1/users/self/';

        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'x-li-format' => 'json',
            ],
            'query' => [
                'access_token' => $token,
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
            'name' => $user['full_name'],
            'email' => null,
            'avatar' => $user['profile_picture'],
            'avatar_original' => null,
        ]);
    }

    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }
}
