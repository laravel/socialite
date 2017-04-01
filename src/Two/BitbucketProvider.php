<?php

namespace Laravel\Socialite\Two;

class BitbucketProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://bitbucket.org/site/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://bitbucket.org/site/oauth2/access_token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.bitbucket.org/2.0/user', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        $user = json_decode($response->getBody(), true);
        $user['email'] = $this->getEmailByToken($token);

        return $user;
    }

    /**
     * Get the email for the given access token.
     *
     * @param  string  $token
     * @return string|null
     */
    protected function getEmailByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.bitbucket.org/2.0/user/emails', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        $response = json_decode($response->getBody(), true);

        foreach ($response['values'] as $email) {
            if ($email['is_primary'] && $email['is_confirmed']) {
                return $email['email'];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['uuid'], 'nickname' => $user['username'], 'name' => array_get($user, 'display_name'),
            'email' => array_get($user, 'email'), 'avatar' => array_get($user, 'links.avatar.href'),
        ]);
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
            parent::getTokenFields($code),
            'grant_type',
            'authorization_code'
        );
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param  string|null  $state
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        $fields = parent::getCodeFields($state);

        // bitbucket does not currently support the scope parameter
        unset($fields['scope']);

        return $fields;
    }
}
