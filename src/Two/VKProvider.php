<?php

namespace Laravel\Socialite\Two;

use Exception;
use GuzzleHttp\ClientInterface;

class VKProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['email'];
    /**
     * The custom parameters to be sent with the request.
     *
     * @var array
     */
    protected $parameters = ['display' => 'popup'];
    /**
     * The needed user fields
     *
     * @var array
     */
    protected $fields = ['screen_name', 'photo_max_orig'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://oauth.vk.com/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://oauth.vk.com/access_token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $fields = implode(',', $this->fields);

        $userUrl = 'https://api.vk.com/method/users.get?fields=' . $fields . '&access_token='.$token;

        $response = $this->getHttpClient()->get($userUrl);

        $user = json_decode($response->getBody(), true);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $response = $this->getTokenResponse($this->getCode());

        $responseBody = $response->getBody();

        $token = $this->parseAccessToken($responseBody);

        $email = $this->parseEmail($responseBody);

        $user = $this->mapUserToObject($this->getUserByToken($token));

        $user->map(['email' => $email]);

        return $user->setToken($token);
    }

    /**
     * Get the response with token and email for the given code.
     *
     * @param  string  $code
     * @return \GuzzleHttp\Client
     */
    public function getTokenResponse($code)
    {
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            $postKey => $this->getTokenFields($code),
        ]);

        return $response;
    }

    protected function parseEmail($body)
    {
        return json_decode($body, true)['email'];
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $response = $user['response'][0];

        return (new User)->setRaw($user)->map([
            'id' => $response['uid'],
            'nickname' => $response['screen_name'],
            'name' => $response['first_name'],
            'avatar' => $response['photo_max_orig'],
        ]);
    }

    /**
     * Add the needed user fields
     *
     * @param  array  $fields
     * @return $this->fields
     */
    public function fields(array $fields)
    {
        foreach ($fields as $field) {
            $this->fields[] = $field;
        }

        return $this->fields;
    }
}
