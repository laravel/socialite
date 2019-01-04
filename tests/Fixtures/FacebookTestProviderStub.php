<?php

namespace Laravel\Socialite\Tests\Fixtures;

use stdClass;
use Mockery as m;
use Laravel\Socialite\Two\FacebookProvider;

class FacebookTestProviderStub extends FacebookProvider
{
    /**
     * @var \GuzzleHttp\Client|\Mockery\MockInterface
     */
    public $http;

    protected function getUserByToken($token)
    {
        return ['id' => 'foo'];
    }

    /**
     * Get a fresh instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client|\Mockery\MockInterface
     */
    protected function getHttpClient()
    {
        if ($this->http) {
            return $this->http;
        }

        return $this->http = m::mock(stdClass::class);
    }
}
