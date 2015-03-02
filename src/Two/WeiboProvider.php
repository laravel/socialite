<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class WeiboProvider extends AbstractProvider implements ProviderInterface
{

    /**
     * The base Weibo base URL.
     *
     * @var string
     */
    protected $baseUrl = 'https://api.weibo.com';

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
    protected $scopes = ['email'];

    
    /**
    * The uid of user authorized.
    */
    protected $uid;

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
        return $this->baseUrl.'/oauth2/access_token';
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
        $this->uid = $body['uid'];
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
        $response = $this->getHttpClient()->get($this->baseUrl. '/' .$this->version. '/users/show.json', [
            'query'=> [
                'access_token' => $token,
                'uid' => $this->uid,
            ],
            'headers' => [
                'Accept' => 'application/json',
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
            'id' => $user['id'], 'nickname' => null, 'name' => $user['name'],
            'email' => isset($user['email']) ? $user['email'] : null, 'avatar' => isset($user['avatar_large']) ? $user['avatar_large'] : null,
        ]);
    }
}
