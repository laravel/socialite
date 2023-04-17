<?php

namespace Laravel\Socialite\Tests;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\One\MissingTemporaryCredentialsException;
use Laravel\Socialite\One\MissingVerifierException;
use Laravel\Socialite\One\User as SocialiteUser;
use Laravel\Socialite\Tests\Fixtures\OAuthOneTestProviderStub;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Twitter;
use League\OAuth1\Client\Server\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class OAuthOneTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function testRedirectGeneratesTheProperIlluminateRedirectResponse()
    {
        $server = m::mock(Twitter::class);
        $temp = m::mock(TemporaryCredentials::class);
        $server->expects('getTemporaryCredentials')->andReturns($temp);
        $server->expects('getAuthorizationUrl')->with($temp)->andReturns('http://auth.url');
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->with('oauth.temp', $temp);

        $provider = new OAuthOneTestProviderStub($request, $server);
        $response = $provider->redirect();

        $this->assertInstanceOf(SymfonyRedirectResponse::class, $response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testUserReturnsAUserInstanceForTheAuthenticatedRequest()
    {
        $server = m::mock(Twitter::class);
        $temp = m::mock(TemporaryCredentials::class);
        $server->expects('getTokenCredentials')->with($temp, 'oauth_token', 'oauth_verifier')->andReturns(
            $token = m::mock(TokenCredentials::class)
        );
        $server->expects('getUserDetails')->with($token, false)->andReturns($user = m::mock(User::class));
        $token->expects('getIdentifier')->twice()->andReturns('identifier');
        $token->expects('getSecret')->twice()->andReturns('secret');
        $user->uid = 'uid';
        $user->email = 'foo@bar.com';
        $user->extra = ['extra' => 'extra'];
        $request = Request::create('foo', 'GET', ['oauth_token' => 'oauth_token', 'oauth_verifier' => 'oauth_verifier']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('get')->with('oauth.temp')->andReturns($temp);

        $provider = new OAuthOneTestProviderStub($request, $server);
        $user = $provider->user();

        $this->assertInstanceOf(SocialiteUser::class, $user);
        $this->assertSame('uid', $user->id);
        $this->assertSame('foo@bar.com', $user->email);
        $this->assertSame(['extra' => 'extra'], $user->user);
    }

    public function testExceptionIsThrownWhenVerifierIsMissing()
    {
        $this->expectException(MissingVerifierException::class);

        $server = m::mock(Twitter::class);
        $request = Request::create('foo');
        $request->setLaravelSession(m::mock(Session::class));

        $provider = new OAuthOneTestProviderStub($request, $server);
        $provider->user();
    }

    public function testExceptionIsThrownWhenTemporaryCredentialsAreMissing()
    {
        $this->expectException(MissingTemporaryCredentialsException::class);

        $server = m::mock(Twitter::class);
        $request = Request::create('foo', 'GET', ['oauth_token' => 'oauth_token', 'oauth_verifier' => 'oauth_verifier']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('get')->with('oauth.temp')->andReturns(null);

        $provider = new OAuthOneTestProviderStub($request, $server);
        $provider->user();
    }
}
