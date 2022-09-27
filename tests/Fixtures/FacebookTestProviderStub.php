<?php

namespace Laravel\Socialite\Tests\Fixtures;

use Laravel\Socialite\Two\FacebookProvider;
use Mockery as m;
use stdClass;

class FacebookTestProviderStub extends FacebookProvider
{
    /**
     * @var \GuzzleHttp\Client|\Mockery\MockInterface
     */
    public \GuzzleHttp\Client|\Mockery\MockInterface $http;

    protected function getUserByToken(string $token): array
    {
        return ['id' => 'foo'];
    }

    /**
     * Get a fresh instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client|\Mockery\MockInterface
     */
    protected function getHttpClient(): \GuzzleHttp\Client|\Mockery\MockInterface
    {
        if ($this->http) {
            return $this->http;
        }

        return $this->http = m::mock(stdClass::class);
    }
}
