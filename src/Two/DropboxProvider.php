<?php

namespace Laravel\Socialite\Two;

class DropboxProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The base Dropbox API URL.
     *
     * @var string
     */
    protected $apiUrl = 'https://api.dropbox.com';

    /**
     * The Dropbox API version for the request.
     *
     * @var string
     */
    protected $version = '1';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://www.dropbox.com/'.$this->version.'/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return $this->apiUrl.'/'.$this->version.'/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'query' => $this->getTokenFields($code),
        ]);

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return [
            'code' => $code, 'grant_type' => 'authorization_code', 'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret, 'redirect_uri' => $this->redirectUrl,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->apiUrl.'/'.$this->version.'/account/info', [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer '. $token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        // Dropbox does not provide any avatar image
        $avatarUrl = null;

        $firstName = isset($user['name_details']['given_name']) ? $user['name_details']['given_name'] : null;

        $lastName = isset($user['name_details']['surname']) ? $user['name_details']['surname'] : null;

        return (new User)->setRaw($user)->map([
            'id' => $user['uid'], 'nickname' => null, 'name' => $firstName.' '.$lastName,
            'email' => isset($user['email']) ? $user['email'] : null, 'avatar' => null,
            'avatar_original' => null,
        ]);
    }
}