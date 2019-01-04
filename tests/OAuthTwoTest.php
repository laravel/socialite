<?php

namespace Laravel\Socialite\Tests;

use stdClass;
use Mockery as m;
use Illuminate\Http\Request;
use GuzzleHttp\ClientInterface;
use Laravel\Socialite\Two\User;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Session\Session;
use Laravel\Socialite\Tests\Fixtures\FacebookTestProviderStub;
use Laravel\Socialite\Tests\Fixtures\OAuthTwoTestProviderStub;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class OAuthTwoTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();

        m::close();
    }

    public function testRedirectGeneratesTheProperIlluminateRedirectResponse()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->shouldReceive('put')->once();
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $response = $provider->redirect();

        $this->assertInstanceOf(SymfonyRedirectResponse::class, $response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('http://auth.url', $response->getTargetUrl());
    }

    public function testUserReturnsAUserInstanceForTheAuthenticatedRequest()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->shouldReceive('pull')->once()->with('state')->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $provider->http = m::mock(stdClass::class);
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';
        $provider->http->shouldReceive('post')->once()->with('http://token.url', [
            'headers' => ['Accept' => 'application/json'], $postKey => ['client_id' => 'client_id', 'client_secret' => 'client_secret', 'code' => 'code', 'redirect_uri' => 'redirect_uri'],
        ])->andReturn($response = m::mock(stdClass::class));
        $response->shouldReceive('getBody')->once()->andReturn('{ "access_token" : "access_token", "refresh_token" : "refresh_token", "expires_in" : 3600 }');
        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('foo', $user->id);
        $this->assertSame('access_token', $user->token);
        $this->assertSame('refresh_token', $user->refreshToken);
        $this->assertSame(3600, $user->expiresIn);
    }

    public function testUserReturnsAUserInstanceForTheAuthenticatedFacebookRequest()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setSession($session = m::mock(SessionInterface::class));
        $session->shouldReceive('pull')->once()->with('state')->andReturn(str_repeat('A', 40));
        $provider = new FacebookTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $provider->http = m::mock(stdClass::class);
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';
        $provider->http->shouldReceive('post')->once()->with('https://graph.facebook.com/v3.0/oauth/access_token', [
            $postKey => ['client_id' => 'client_id', 'client_secret' => 'client_secret', 'code' => 'code', 'redirect_uri' => 'redirect_uri'],
        ])->andReturn($response = m::mock(stdClass::class));
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(['access_token' => 'access_token', 'expires' => 5183085]));
        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('foo', $user->id);
        $this->assertSame('access_token', $user->token);
        $this->assertNull($user->refreshToken);
        $this->assertEquals(5183085, $user->expiresIn);
    }

    /**
     * @expectedException \Laravel\Socialite\Two\InvalidStateException
     */
    public function testExceptionIsThrownIfStateIsInvalid()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('B', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->shouldReceive('pull')->once()->with('state')->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $provider->user();
    }

    /**
     * @expectedException \Laravel\Socialite\Two\InvalidStateException
     */
    public function testExceptionIsThrownIfStateIsNotSet()
    {
        $request = Request::create('foo', 'GET', ['state' => 'state', 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->shouldReceive('pull')->once()->with('state');
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $provider->user();
    }
}
