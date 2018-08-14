<?php

namespace Tests;

use Mockery as m;
use Illuminate\Http\Request;
use PHPUnit_Framework_TestCase;
use Tests\Fixtures\OAuthOneTestProviderStub;

class OAuthOneTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testRedirectGeneratesTheProperIlluminateRedirectResponse()
    {
        $server = m::mock(\League\OAuth1\Client\Server\Twitter::class);
        $server->shouldReceive('getTemporaryCredentials')->once()->andReturn('temp');
        $server->shouldReceive('getAuthorizationUrl')->once()->with('temp')->andReturn('http://auth.url');
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(\Illuminate\Contracts\Session\Session::class));
        $session->shouldReceive('put')->once()->with('oauth.temp', 'temp');

        $provider = new OAuthOneTestProviderStub($request, $server);
        $response = $provider->redirect();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
    }

    public function testUserReturnsAUserInstanceForTheAuthenticatedRequest()
    {
        $server = m::mock(\League\OAuth1\Client\Server\Twitter::class);
        $temp = m::mock(\League\OAuth1\Client\Credentials\TemporaryCredentials::class);
        $server->shouldReceive('getTokenCredentials')->once()->with($temp, 'oauth_token', 'oauth_verifier')->andReturn(
            $token = m::mock(\League\OAuth1\Client\Credentials\TokenCredentials::class)
        );
        $server->shouldReceive('getUserDetails')->once()->with($token, false)->andReturn($user = m::mock(\League\OAuth1\Client\Server\User::class));
        $token->shouldReceive('getIdentifier')->twice()->andReturn('identifier');
        $token->shouldReceive('getSecret')->twice()->andReturn('secret');
        $user->uid = 'uid';
        $user->email = 'foo@bar.com';
        $user->extra = ['extra' => 'extra'];
        $request = Request::create('foo', 'GET', ['oauth_token' => 'oauth_token', 'oauth_verifier' => 'oauth_verifier']);
        $request->setLaravelSession($session = m::mock(\Illuminate\Contracts\Session\Session::class));
        $session->shouldReceive('get')->once()->with('oauth.temp')->andReturn($temp);

        $provider = new OAuthOneTestProviderStub($request, $server);
        $user = $provider->user();

        $this->assertInstanceOf('Laravel\Socialite\One\User', $user);
        $this->assertSame('uid', $user->id);
        $this->assertSame('foo@bar.com', $user->email);
        $this->assertSame(['extra' => 'extra'], $user->user);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionIsThrownWhenVerifierIsMissing()
    {
        $server = m::mock(\League\OAuth1\Client\Server\Twitter::class);
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(\Illuminate\Contracts\Session\Session::class));

        $provider = new OAuthOneTestProviderStub($request, $server);
        $provider->user();
    }
}
