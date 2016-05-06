<?php

use Mockery as m;
use Illuminate\Http\Request;
use GuzzleHttp\ClientInterface;
use Laravel\Socialite\Two\User;
use Laravel\Socialite\Two\AbstractProvider;

class OAuthTwoTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testRedirectGeneratesTheProperSymfonyRedirectResponse()
    {
        $request = Request::create('foo');
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
        $session->shouldReceive('set')->once();
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $response = $provider->redirect();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('http://auth.url', $response->getTargetUrl());
    }

    public function getMockResponses()
    {
        return [
            ['application/json', '{ "access_token" : "access_token", "refresh_token" : "refresh_token", "expires_in" : 3600 }'],
            ['application/x-www-form-urlencoded', 'access_token=access_token&refresh_token=refresh_token&expires_in=3600'],
        ];
    }

    /**
     * @dataProvider getMockResponses
     */
    public function testUserReturnsAUserInstanceForTheAuthenticatedRequest($contentType, $responseBody)
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
        $session->shouldReceive('pull')->once()->with('state')->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $provider->http = m::mock('StdClass');
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';
        $provider->http->shouldReceive('post')->once()->with('http://token.url', [
            'headers' => ['Accept' => 'application/json'], $postKey => ['client_id' => 'client_id', 'client_secret' => 'client_secret', 'code' => 'code', 'redirect_uri' => 'redirect_uri'],
        ])->andReturn($response = m::mock('StdClass'));
        $response->shouldReceive('getHeader')->once()->with('Content-Type')->andReturn([$contentType]);
        $response->shouldReceive('getBody')->once()->andReturn($responseBody);
        $user = $provider->user();

        $this->assertInstanceOf('Laravel\Socialite\Two\User', $user);
        $this->assertEquals('foo', $user->id);
        $this->assertEquals('access_token', $user->token);
        $this->assertEquals('refresh_token', $user->refreshToken);
        $this->assertEquals(3600, $user->expiresIn);
    }

    /**
     * @expectedException Laravel\Socialite\Two\InvalidStateException
     */
    public function testExceptionIsThrownIfStateIsInvalid()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('B', 40), 'code' => 'code']);
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
        $session->shouldReceive('pull')->once()->with('state')->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $user = $provider->user();
    }

    /**
     * @expectedException Laravel\Socialite\Two\InvalidStateException
     */
    public function testExceptionIsThrownIfStateIsNotSet()
    {
        $request = Request::create('foo', 'GET', ['state' => 'state', 'code' => 'code']);
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
        $session->shouldReceive('pull')->once()->with('state');
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $user = $provider->user();
    }
}

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
