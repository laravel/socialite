<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class MailchimpProvider extends AbstractProvider implements ProviderInterface
{

    /**
     * The base Weibo base URL.
     *
     * @var string
     */
    protected $baseUrl = 'https://login.mailchimp.com';

    /**
     * The API version for the request.
     *
     * @var string
     */
    protected $version = '2';

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth2/authorize', $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->baseUrl.'/oauth2/token';
    }

    /**
     * Get the access token for the given code.
     *
     * @param  string  $code
     * @return string
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'body' => $this->getTokenFields($code),
        ]);
        return $this->parseAccessToken($response->getBody());
    }

    
    /**
    * Get the Post fields for the token request
    *
    * @param string $code
    * @return array
    */
    protected function getTokenFields($code)
    {
        return array_add(
            parent::getTokenFields($code), 'grant_type', 'authorization_code'
        );
    }

    /**
     * Get the access token for the given code.
     *
     * @param  string  $code
     * @return string
     */
    protected function parseAccessToken($body)
    {
        $body = json_decode($body, true);
        return $body['access_token'];
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param  string  $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->baseUrl.'/oauth2/metadata', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'OAuth '.$token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array  $user
     * @return \Laravel\Socialite\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['user_id'], 'nickname' => null, 'name' => $user['accountname'],
            'email' => isset($user['login']['login_email']) ? $user['login']['login_email'] : null, 'avatar' => isset($user['avatar']) ? $user['avatar'] : null,
            'api_endpoint' => $user['api_endpoint'],
            'dc' => $user['dc'],
        ]);
    }
}