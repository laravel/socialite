<?php

namespace Laravel\Socialite\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Laravel\Socialite\Tests\Fixtures\GoogleTestProviderStub;
use Laravel\Socialite\Two\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class GoogleProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function test_it_can_map_a_user_from_an_access_token()
    {
        $request = Request::create('/');

        $provider = new GoogleTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');

        $provider->http = m::mock(Client::class);

        $provider->http->allows('get')->with('https://www.googleapis.com/oauth2/v3/userinfo', [
            RequestOptions::QUERY => [
                'prettyPrint' => 'false',
            ],
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer fake-token',
            ],
        ])->andReturns($response = m::mock(ResponseInterface::class));

        $response->allows('getBody')->andReturns(m::mock(StreamInterface::class));

        $user = $provider->userFromToken('fake-token');

        $this->assertInstanceOf(User::class, $user);
    }
}
