<?php

namespace Laravel\Socialite\Two;

use GuzzleHttp\ClientInterface;

class WindowsProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The base Windows Live API URL.
     *
     * @var string
     */
    protected $baseUrl = 'https://apis.live.net';

    /**
     * The Graph API version for the request.
     *
     * @var string
     */
    protected $version = 'v5.0';

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [
        'wl.signin',
        'wl.basic',
        'wl.emails',
    ];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://login.live.com/oauth20_authorize.srf', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://login.live.com/oauth20_token.srf';
    }

    /**
     * Get the access token for the given code.
     *
     * @param  string  $code
     * @return string
     */
    public function getAccessToken($code)
    {
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            $postKey => $this->getTokenFields($code),
        ]);

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return array_add(
            parent::getTokenFields($code), 'grant_type', 'authorization_code'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $appSecretProof = hash_hmac('sha256', $token, $this->clientSecret);

        $response = $this->getHttpClient()->get($this->baseUrl.'/'.$this->version.'/me?access_token='.$token, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'], 'nickname' => null, 'name' => isset($user['name']) ? $user['name'] : null,
            'email' => isset($user['emails']['preferred']) ? $user['emails']['preferred'] : null,
            'avatar' => $this->baseUrl.'/'.$this->version.'/'.$user['id'].'/picture',
        ]);
    }
}
