<?php

namespace Tests\Fixtures;

use Mockery as m;
use Laravel\Socialite\Two\User;
use Laravel\Socialite\Two\AbstractProvider;

class OAuthTwoTestProviderStub extends AbstractProvider
{
    public $http;

    protected function getAuthUrl($state)
    {
        return 'http://auth.url';
    }

    protected function getTokenUrl()
    {
        return 'http://token.url';
    }

    protected function getUserByToken($token)
    {
        return ['id' => 'foo'];
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->map(['id' => $user['id']]);
    }

    /**
     * Get a fresh instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if ($this->http) {
            return $this->http;
        }

        return $this->http = m::mock('StdClass');
    }
}
