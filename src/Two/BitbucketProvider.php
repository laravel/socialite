<?php

namespace Laravel\Socialite\Two;

use Exception;

class BitbucketProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['email'];

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
        $userUrl = 'https://bitbucket.org/api/1.0/user/?access_token='.$token;

        $response = $this->getHttpClient()->get(
            $userUrl, $this->getRequestOptions()
        );

        $user = json_decode($response->getBody(), true);

        // Fetch uuid from 2.0 API
        $userUrl = 'https://api.bitbucket.org/2.0/users/'.array_get($user,'user.username').'?access_token='.$token;
        $response = $this->getHttpClient()->get(
            $userUrl, $this->getRequestOptions()
        );

        $additionalUserData = json_decode($response->getBody(), true);

        $user['id'] = array_get($additionalUserData,'uuid');

        if (in_array('email', $this->scopes)) {
            $user['email'] = $this->getEmailByUsernameAndToken(array_get($user,'user.username'),$token);
        }

        return $user;
    }

    /**
     * Get the email for the given access token.
     *
     * @param  string $username
     * @param  string $token
     * @return null|string
     */
    protected function getEmailByUsernameAndToken($username, $token)
    {
        $emailsUrl = 'https://bitbucket.org/api/1.0/users/'.$username.'/emails?access_token='.$token;

        try {
            $response = $this->getHttpClient()->get(
                $emailsUrl, $this->getRequestOptions()
            );
        } catch (Exception $e) {
            return;
        }

        foreach (json_decode($response->getBody(), true) as $email) {
            if ($email['primary'] && $email['active']) {
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
            'id' => $user['id'], 'nickname' => array_get($user, 'user.username'), 'name' => array_get($user, 'user.display_name'),
            'email' => $user['email'] , 'avatar' => array_get($user, 'user.avatar')
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
            parent::getTokenFields($code), 'grant_type', 'authorization_code'
        );
    }

    /**
     * Get the default options for an HTTP request.
     *
     * @return array
     */
    protected function getRequestOptions()
    {
        return [];
    }
}
