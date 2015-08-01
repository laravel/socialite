<?php

use Mockery as m;
use Illuminate\Http\Request;
use Laravel\Socialite\One\AbstractProvider;

class OAuthOneTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testRedirectGeneratesTheProperSymfonyRedirectResponse()
    {
        $server = m::mock('League\OAuth1\Client\Server\Twitter');
        $server->shouldReceive('getTemporaryCredentials')->once()->andReturn('temp');
        $server->shouldReceive('getAuthorizationUrl')->once()->with('temp')->andReturn('http://auth.url');
        $request = Request::create('foo');
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
        $session->shouldReceive('set')->once()->with('oauth.temp', 'temp');

        $provider = new OAuthOneTestProviderStub($request, $server);
        $response = $provider->redirect();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
    }

    public function testUserReturnsAUserInstanceForTheAuthenticatedRequest()
    {
        $server = m::mock('League\OAuth1\Client\Server\Twitter');
        $temp = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials');
        $server->shouldReceive('getTokenCredentials')->once()->with($temp, 'oauth_token', 'oauth_verifier')->andReturn(
            $token = m::mock('League\OAuth1\Client\Credentials\TokenCredentials')
        );
        $server->shouldReceive('getUserDetails')->once()->with($token)->andReturn($user = m::mock('League\OAuth1\Client\Server\User'));
        $token->shouldReceive('getIdentifier')->once()->andReturn('identifier');
        $token->shouldReceive('getSecret')->once()->andReturn('secret');
        $user->uid = 'uid';
        $user->email = 'foo@bar.com';
        $user->extra = ['extra' => 'extra'];
        $request = Request::create('foo', 'GET', ['oauth_token' => 'oauth_token', 'oauth_verifier' => 'oauth_verifier']);
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
        $session->shouldReceive('get')->once()->with('oauth.temp')->andReturn($temp);

        $provider = new OAuthOneTestProviderStub($request, $server);
        $user = $provider->user();

        $this->assertInstanceOf('Laravel\Socialite\One\User', $user);
        $this->assertEquals('uid', $user->id);
        $this->assertEquals('foo@bar.com', $user->email);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIsThrownWhenVerifierIsMissing()
    {
        $server = m::mock('League\OAuth1\Client\Server\Twitter');
        $request = Request::create('foo');
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));

        $provider = new OAuthOneTestProviderStub($request, $server);
        $user = $provider->user();
    }
}

class OAuthOneTestProviderStub extends AbstractProvider
{
}
