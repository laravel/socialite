<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class VkProvider extends AbstractProvider implements ProviderInterface {
    protected $email;
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['friends,email,offline'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('http://oauth.vk.com/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        $url = 'https://oauth.vk.com/access_token?'.http_build_query([
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri'  => $this->redirectUrl]);
        return $url;
    }

    /**
     * Get the access token for the given code.
     *
     * @param  string  $code
     * @return string
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), ['query' => $this->getTokenFields($code),]);
        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessToken($body)
    {
        $json = json_decode($body, true);
        $this->email = $json['email'];
        $access_token = $json['access_token'];
        return $access_token;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $url = 'https://api.vk.com/method/users.get?'.http_build_query([
            'access_token' => $token,
            'fields' => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big']);
        $response = $this->getHttpClient()->get($url);
        $info = (json_decode($response->getBody(), true));
        return $info['response'][0];
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['uid'], 'nickname' => $user['screen_name'], 'name' => $user['first_name'].' '.$user['last_name'],
            'email' => $this->email, 'avatar' => $user['photo_big'],
        ]);
    }

}
