<?php

namespace Laravel\Socialite\Two;

use Illuminate\Support\Arr;

class TwitchProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['user:read:email'];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = '+';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://id.twitch.tv/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://id.twitch.tv/oauth2/token';
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return parent::getTokenFields($code) + ['grant_type' => 'authorization_code'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        return $this->getUserProfile($token);
    }

    /**
     * Get the profile fields for the user.
     *
     * @param  string  $token
     * @return array
     */
    protected function getUserProfile($token)
    {
        $url = 'https://api.twitch.tv/helix/users';

        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'client-id' => $this->clientId
            ],
        ]);

        return (array) json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'data.0.id'),
            'nickname' => Arr::get($user, 'data.0.login'),
            'name' => Arr::get($user, 'data.0.display_name'),
            'email' => Arr::get($user, 'data.0.email'),
            'avatar' => Arr::get($user, 'data.0.profile_image_url')
        ]);
    }
}
