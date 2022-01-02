<?php

namespace Laravel\Socialite\Two;

use Illuminate\Support\Arr;

class SpotifyProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['user-read-private', 'user-read-email'];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    public function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://accounts.spotify.com/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://accounts.spotify.com/api/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {

        $response = $this->getHttpClient()->get('https://api.spotify.com/v1/me', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ]
        ]);

        return (array) json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'name' => $user['display_name'],
            'email' => $user['email'],
            'avatar' => Arr::get($user, 'images.0.url'),
            'avatar_original' => Arr::get($user, 'images.0.url'),
        ]);
    }
}
