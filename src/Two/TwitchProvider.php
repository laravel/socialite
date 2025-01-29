<?php

namespace Laravel\Socialite\Two;

use Exception;
use GuzzleHttp\RequestOptions;
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
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://api.twitch.tv/helix/users',
            [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$token,
                    'Client-ID' => $this->clientId,
                ],
            ]
        );

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $user = $user['data']['0'];

        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => $user['display_name'],
            'name' => $user['display_name'],
            'email' => Arr::get($user, 'email'),
            'avatar' => $user['profile_image_url'],
        ]);
    }

    /**
     * Get the default options for an HTTP request.
     *
     * @param  string  $token
     * @return array
     */
    protected function getRequestOptions($token)
    {
        return [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'token '.$token,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken($refreshToken)
    {
        $response = $this->getRefreshTokenResponse($refreshToken);

        return new Token(
            Arr::get($response, 'access_token'),
            Arr::get($response, 'refresh_token'),
            Arr::get($response, 'expires_in'),
            Arr::get($response, 'scope', [])
        );
    }
}
