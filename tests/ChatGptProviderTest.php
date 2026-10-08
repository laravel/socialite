<?php

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\User as UserContract;
use Laravel\Socialite\Two\ChatGptProvider;
use Laravel\Socialite\Two\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ChatGptProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function test_response()
    {
        $user = $this->fromResponse([
            'sub' => 'user-abc123',
            'nickname' => 'taylor',
            'picture' => 'https://example.com/avatar.png',
            'name' => 'Taylor Otwell',
            'email' => 'taylor@example.com',
            'email_verified' => true,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('user-abc123', $user->getId());
        $this->assertSame('taylor', $user->getNickname());
        $this->assertSame('Taylor Otwell', $user->getName());
        $this->assertSame('taylor@example.com', $user->getEmail());
        $this->assertSame('https://example.com/avatar.png', $user->getAvatar());

        $this->assertSame([
            'id' => 'user-abc123',
            'nickname' => 'taylor',
            'name' => 'Taylor Otwell',
            'email' => 'taylor@example.com',
            'email_verified' => true,
            'avatar' => 'https://example.com/avatar.png',
        ], $user->attributes);
    }

    public function test_missing_optional_claims()
    {
        $user = $this->fromResponse([
            'sub' => 'user-abc123',
            'name' => 'Taylor Otwell',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('user-abc123', $user->getId());
        $this->assertNull($user->getNickname());
        $this->assertSame('Taylor Otwell', $user->getName());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getAvatar());

        $this->assertSame([
            'id' => 'user-abc123',
            'nickname' => null,
            'name' => 'Taylor Otwell',
            'email' => null,
            'email_verified' => null,
            'avatar' => null,
        ], $user->attributes);
    }

    public function test_nickname_falls_back_to_preferred_username()
    {
        $user = $this->fromResponse([
            'sub' => 'user-abc123',
            'nickname' => null,
            'preferred_username' => 'taylor',
        ]);

        $this->assertSame('taylor', $user->getNickname());
    }

    public function test_redirect_uses_pkce_and_openid_scopes()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->with('state', m::type('string'));
        $session->expects('put')->with('code_verifier', m::type('string'));
        $session->expects('get')->with('code_verifier')->andReturns('verifier');

        $provider = new ChatGptProvider($request, 'client_id', 'client_secret', 'redirect');
        $url = $provider->redirect()->getTargetUrl();

        $this->assertStringStartsWith('https://auth.openai.com/api/accounts/authorize?', $url);
        $this->assertStringContainsString('scope=openid+profile+email', $url);
        $this->assertStringContainsString('code_challenge_method=S256', $url);
    }

    protected function fromResponse(array $response): UserContract
    {
        $request = Request::create('foo', 'GET', ['code' => 'fake-code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->allows('pull')->with('code_verifier')->andReturns('verifier');

        $stream = m::mock(StreamInterface::class);
        $stream->allows('__toString')->andReturns(json_encode(['access_token' => 'fake-token']));

        $accessTokenResponse = m::mock(ResponseInterface::class);
        $accessTokenResponse->allows('getBody')->andReturns($stream);

        $basicProfileStream = m::mock(StreamInterface::class);
        $basicProfileStream->allows('__toString')->andReturns(json_encode($response));

        $basicProfileResponse = m::mock(ResponseInterface::class);
        $basicProfileResponse->allows('getBody')->andReturns($basicProfileStream);

        $guzzle = m::mock(Client::class);
        $guzzle->expects('post')->with('https://auth.openai.com/api/accounts/oauth/token', m::on(
            fn ($options) => $options[RequestOptions::FORM_PARAMS]['code_verifier'] === 'verifier'
        ))->andReturns($accessTokenResponse);
        $guzzle->allows('get')->with('https://auth.openai.com/api/accounts/oauth/userinfo', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer fake-token',
            ],
        ])->andReturns($basicProfileResponse);

        $provider = new ChatGptProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->stateless();
        $provider->setHttpClient($guzzle);

        return $provider->user();
    }
}
