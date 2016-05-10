<?php

namespace Laravel\Socialite\Two;

use Exception;

class TwitchProvider extends AbstractProvider implements ProviderInterface
{

    /**
     * Api domain
     *
     * @var string
     */
    private $apiDomain = 'https://api.twitch.tv';

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['user_read'];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->apiDomain.'/kraken/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return $this->apiDomain.'/kraken/oauth2/token';
    }

    /**
     * Get the POST fields for the token request.
     * https://github.com/justintv/Twitch-API/blob/master/authentication.md
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUrl,
            'code' => $code,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $userUrl = $this->apiDomain.'/kraken/user?oauth_token='.$token;

        $response = $this->getHttpClient()->get(
            $userUrl, $this->getRequestOptions()
        );

        $user = json_decode($response->getBody(), true);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['_id'], 'nickname' => $user['display_name'], 'name' => $user['name'],
            'email' => $user['email'], 'avatar' => $user['logo']
        ]);
    }

    /**
     * Get the default options for an HTTP request.
     *
     * @return array
     */
    protected function getRequestOptions()
    {
        return [
            'headers' => [
                'Accept' => 'application/vnd.twitchtv.v3+json',
            ],
        ];
    }
}
