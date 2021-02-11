<?php

namespace Laravel\Socialite\Tests;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Tests\Fixtures\FacebookTestProviderStub;
use Laravel\Socialite\Tests\Fixtures\OAuthTwoTestProviderStub;
use Laravel\Socialite\Tests\Fixtures\OAuthTwoWithPKCETestProviderStub;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OAuthTwoTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function testRedirectGeneratesTheProperIlluminateRedirectResponseWithoutPKCE()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));

        $state = null;
        $closure = function ($name, $stateInput) use (&$state) {
            if ($name === 'state') {
                $state = $stateInput;

                return true;
            }

            return false;
        };

        $session->shouldReceive('put')->once()->withArgs($closure);
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $response = $provider->redirect();

        $this->assertInstanceOf(SymfonyRedirectResponse::class, $response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('http://auth.url?client_id=client_id&redirect_uri=redirect&scope=&response_type=code&state='.$state, $response->getTargetUrl());
    }

    private static $codeVerifier = null;

    public function testRedirectGeneratesTheProperIlluminateRedirectResponseWithPKCE()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));

        $state = null;
        $sessionPutClosure = function ($name, $value) use (&$state) {
            if ($name === 'state') {
                $state = $value;

                return true;
            } elseif ($name === 'code_verifier') {
                self::$codeVerifier = $value;

                return true;
            }

            return false;
        };

        $sessionPullClosure = function ($name) {
            if ($name === 'code_verifier') {
                return self::$codeVerifier;
            }
        };

        $session->shouldReceive('put')->twice()->withArgs($sessionPutClosure);
        $session->shouldReceive('pull')->once()->with('code_verifier')->andReturnUsing($sessionPullClosure);

        $provider = new OAuthTwoWithPKCETestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $response = $provider->redirect();

        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', self::$codeVerifier, true)), '+/', '-_'), '=');

        $this->assertInstanceOf(SymfonyRedirectResponse::class, $response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('http://auth.url?client_id=client_id&redirect_uri=redirect&scope=&response_type=code&state='.$state.'&code_challenge='.$codeChallenge.'&code_challenge_method=S256', $response->getTargetUrl());
    }

    public function testUserReturnsAUserInstanceForTheAuthenticatedRequest()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->shouldReceive('pull')->once()->with('state')->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $provider->http = m::mock(stdClass::class);
        $provider->http->shouldReceive('post')->once()->with('http://token.url', [
            'headers' => ['Accept' => 'application/json'], 'form_params' => ['grant_type' => 'authorization_code', 'client_id' => 'client_id', 'client_secret' => 'client_secret', 'code' => 'code', 'redirect_uri' => 'redirect_uri'],
        ])->andReturn($response = m::mock(stdClass::class));
        $response->shouldReceive('getBody')->once()->andReturn('{ "access_token" : "access_token", "refresh_token" : "refresh_token", "expires_in" : 3600 }');
        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('foo', $user->id);
        $this->assertSame('access_token', $user->token);
        $this->assertSame('refresh_token', $user->refreshToken);
        $this->assertSame(3600, $user->expiresIn);
        $this->assertSame($user->id, $provider->user()->id);
    }

    public function testUserReturnsAUserInstanceForTheAuthenticatedFacebookRequest()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setSession($session = m::mock(SessionInterface::class));
        $session->shouldReceive('pull')->once()->with('state')->andReturn(str_repeat('A', 40));
        $provider = new FacebookTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $provider->http = m::mock(stdClass::class);
        $provider->http->shouldReceive('post')->once()->with('https://graph.facebook.com/v3.3/oauth/access_token', [
            'form_params' => ['grant_type' => 'authorization_code', 'client_id' => 'client_id', 'client_secret' => 'client_secret', 'code' => 'code', 'redirect_uri' => 'redirect_uri'],
        ])->andReturn($response = m::mock(stdClass::class));
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(['access_token' => 'access_token', 'expires' => 5183085]));
        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('foo', $user->id);
        $this->assertSame('access_token', $user->token);
        $this->assertNull($user->refreshToken);
        $this->assertSame(5183085, $user->expiresIn);
        $this->assertSame($user->id, $provider->user()->id);
    }

    public function testExceptionIsThrownIfStateIsInvalid()
    {
        $this->expectException(InvalidStateException::class);

        $request = Request::create('foo', 'GET', ['state' => str_repeat('B', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->shouldReceive('pull')->once()->with('state')->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $provider->user();
    }

    public function testExceptionIsThrownIfStateIsNotSet()
    {
        $this->expectException(InvalidStateException::class);

        $request = Request::create('foo', 'GET', ['state' => 'state', 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->shouldReceive('pull')->once()->with('state');
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $provider->user();
    }
}
