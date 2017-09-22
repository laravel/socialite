<?php

namespace Laravel\Socialite\Two;

use Illuminate\Support\Arr;

class SlackProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [
        'identity.basic',
        'identity.email',
        'identity.avatar',
    ];

    /**
     * Get the access token response for the given code.
     *
     * @param  string  $code
     * @return array
     */
    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'query' => $this->getTokenFields($code),
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://slack.com/oauth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://slack.com/api/oauth.access';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://slack.com/api/users.identity', [
            'query' => [
                'token' => $token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        if (! Arr::get($user, 'ok', false)) {
            throw new InvalidUserException;
        }

        return (new User)->setRaw($user['user'])->map([
            'id' => $user['user']['id'], 'nickname' => null, 'name' => Arr::get($user['user'], 'name'),
            'email' => Arr::get($user['user'], 'email'), 'avatar' => Arr::get($user['user'], 'image_48'),
        ]);
    }
}
