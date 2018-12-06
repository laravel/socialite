<?php

namespace Laravel\Socialite\Tests;

use Mockery as m;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\RedirectResponse;
use League\OAuth1\Client\Server\User;
use League\OAuth1\Client\Server\Twitter;
use Illuminate\Contracts\Session\Session;
use Laravel\Socialite\One\User as SocialiteUser;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use Laravel\Socialite\Tests\Fixtures\OAuthOneTestProviderStub;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class OAuthOneTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();

        m::close();
    }

    public function testRedirectGeneratesTheProperIlluminateRedirectResponse()
    {
        $server = m::mock(Twitter::class);
        $server->shouldReceive('getTemporaryCredentials')->once()->andReturn('temp');
        $server->shouldReceive('getAuthorizationUrl')->once()->with('temp')->andReturn('http://auth.url');
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->shouldReceive('put')->once()->with('oauth.temp', 'temp');

        $provider = new OAuthOneTestProviderStub($request, $server);
        $response = $provider->redirect();

        $this->assertInstanceOf(SymfonyRedirectResponse::class, $response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testUserReturnsAUserInstanceForTheAuthenticatedRequest()
    {
        $server = m::mock(Twitter::class);
        $temp = m::mock(TemporaryCredentials::class);
        $server->shouldReceive('getTokenCredentials')->once()->with($temp, 'oauth_token', 'oauth_verifier')->andReturn(
            $token = m::mock(TokenCredentials::class)
        );
        $server->shouldReceive('getUserDetails')->once()->with($token, false)->andReturn($user = m::mock(User::class));
        $token->shouldReceive('getIdentifier')->twice()->andReturn('identifier');
        $token->shouldReceive('getSecret')->twice()->andReturn('secret');
        $user->uid = 'uid';
        $user->email = 'foo@bar.com';
        $user->extra = ['extra' => 'extra'];
        $request = Request::create('foo', 'GET', ['oauth_token' => 'oauth_token', 'oauth_verifier' => 'oauth_verifier']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->shouldReceive('get')->once()->with('oauth.temp')->andReturn($temp);

        $provider = new OAuthOneTestProviderStub($request, $server);
        $user = $provider->user();

        $this->assertInstanceOf(SocialiteUser::class, $user);
        $this->assertSame('uid', $user->id);
        $this->assertSame('foo@bar.com', $user->email);
        $this->assertSame(['extra' => 'extra'], $user->user);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionIsThrownWhenVerifierIsMissing()
    {
        $server = m::mock(Twitter::class);
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));

        $provider = new OAuthOneTestProviderStub($request, $server);
        $provider->user();
    }
}
