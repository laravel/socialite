<?php

use Firebase\JWT\JWT;
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

    public function test_user_from_id_token_when_access_token_is_missing()
    {
        $user = $this->fromIdTokenResponse([
            'iss' => 'https://auth.openai.com',
            'aud' => ['client_id'],
            'sub' => 'user-abc123',
            'name' => 'Taylor Otwell',
            'email' => 'taylor@example.com',
            'exp' => time() + 3600,
        ]);

        $this->assertSame('user-abc123', $user->getId());
        $this->assertSame('Taylor Otwell', $user->getName());
        $this->assertSame('taylor@example.com', $user->getEmail());
        $this->assertNull($user->token);
    }

    public function test_id_token_with_invalid_issuer_is_rejected()
    {
        $this->expectExceptionMessage('Failed to verify ChatGPT ID token: Invalid ID token issuer.');

        $this->fromIdTokenResponse([
            'iss' => 'https://evil.example.com',
            'aud' => 'client_id',
            'sub' => 'user-abc123',
            'exp' => time() + 3600,
        ]);
    }

    public function test_id_token_with_invalid_audience_is_rejected()
    {
        $this->expectExceptionMessage('Failed to verify ChatGPT ID token: Invalid ID token audience.');

        $this->fromIdTokenResponse([
            'iss' => 'https://auth.openai.com',
            'aud' => 'other_client',
            'sub' => 'user-abc123',
            'exp' => time() + 3600,
        ]);
    }

    protected function fromIdTokenResponse(array $claims): UserContract
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $details = openssl_pkey_get_details($key);
        $encode = fn ($value) => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');

        $jwks = ['keys' => [[
            'kid' => 'test-key',
            'kty' => 'RSA',
            'alg' => 'RS256',
            'n' => $encode($details['rsa']['n']),
            'e' => $encode($details['rsa']['e']),
        ]]];

        $request = Request::create('foo', 'GET', ['code' => 'fake-code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->allows('pull')->with('code_verifier')->andReturns('verifier');

        $guzzle = m::mock(Client::class);
        $guzzle->expects('post')->andReturns($this->jsonResponse([
            'id_token' => JWT::encode($claims, $key, 'RS256', 'test-key'),
        ]));
        $guzzle->expects('get')->with('https://auth.openai.com/.well-known/jwks.json')->andReturns($this->jsonResponse($jwks));

        $provider = new ChatGptProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->stateless();
        $provider->setHttpClient($guzzle);

        return $provider->user();
    }

    protected function jsonResponse(array $data): ResponseInterface
    {
        $stream = m::mock(StreamInterface::class);
        $stream->allows('__toString')->andReturns(json_encode($data));

        $response = m::mock(ResponseInterface::class);
        $response->allows('getBody')->andReturns($stream);

        return $response;
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
