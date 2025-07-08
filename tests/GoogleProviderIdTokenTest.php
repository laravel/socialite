<?php

namespace Laravel\Socialite\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class GoogleProviderIdTokenTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function test_it_can_detect_jwt_tokens()
    {
        $provider = $this->getProvider();

        $jwtToken = $this->createMockJwtToken();
        $accessToken = 'ya29.a0AfH6SMCxyz123456789';

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('isJwtToken');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($provider, $jwtToken));
        $this->assertFalse($method->invoke($provider, $accessToken));
    }

    public function test_it_uses_jwt_verification_for_id_tokens()
    {
        $provider = $this->getProvider();
        $idToken = $this->createMockJwtToken();

        $this->mockJwksResponse($provider);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Failed to verify Google JWT token/');

        $provider->userFromToken($idToken);
    }

    public function test_it_falls_back_to_api_call_for_access_tokens()
    {
        $provider = $this->getProvider();
        $accessToken = 'ya29.a0AfH6SMCxyz123456789';

        $httpClient = m::mock(Client::class);
        $provider->setHttpClient($httpClient);

        $response = m::mock(ResponseInterface::class);
        $stream = m::mock(StreamInterface::class);

        $mockUserData = [
            'sub' => '123456789',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'picture' => 'https://example.com/photo.jpg',
        ];

        $httpClient
            ->shouldReceive('get')
            ->with('https://www.googleapis.com/oauth2/v3/userinfo', [
                RequestOptions::QUERY => [
                    'prettyPrint' => 'false',
                ],
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$accessToken,
                ],
            ])
            ->once()
            ->andReturn($response);

        $response->shouldReceive('getBody')->once()->andReturn($stream);

        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn(json_encode($mockUserData));

        $user = $provider->userFromToken($accessToken);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('123456789', $user->getId());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('Test User', $user->getName());
    }

    /**
     * @dataProvider invalidJwtProvider
     */
    public function test_it_handles_invalid_jwt_tokens($description, $tokenOverrides, $expectedException = true)
    {
        $provider = $this->getProvider();
        $invalidToken = $this->createInvalidJwtToken($tokenOverrides);

        if ($expectedException) {
            $this->mockJwksResponse($provider);
            $this->expectException(\Exception::class);
            $this->expectExceptionMessageMatches('/Failed to verify Google JWT token/');
        }

        $provider->userFromToken($invalidToken);
    }

    public static function invalidJwtProvider()
    {
        return [
            'invalid issuer' => [
                'Invalid issuer',
                ['payload' => ['iss' => 'https://invalid-issuer.com']],
            ],
            'invalid audience' => [
                'Invalid audience',
                ['payload' => ['aud' => 'wrong-client-id']],
            ],
            'missing key id' => [
                'Missing key ID',
                ['header' => ['kid' => null]],
            ],
        ];
    }

    public function test_user_mapping_works_with_id_token_format()
    {
        $provider = $this->getProvider();

        $idTokenUser = [
            'sub' => '123456789012345678901',
            'email' => 'testuser@gmail.com',
            'email_verified' => true,
            'name' => 'Test User',
            'picture' => 'https://lh3.googleusercontent.com/photo.jpg',
        ];

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('mapUserToObject');
        $method->setAccessible(true);

        $user = $method->invoke($provider, $idTokenUser);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('123456789012345678901', $user->getId());
        $this->assertEquals('testuser@gmail.com', $user->getEmail());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('https://lh3.googleusercontent.com/photo.jpg', $user->getAvatar());

        $rawUser = $user->getRaw();
        $this->assertEquals('123456789012345678901', $rawUser['id']);
        $this->assertTrue($rawUser['verified_email']);
    }

    /**
     * Get a GoogleProvider instance for testing.
     */
    protected function getProvider()
    {
        $request = Request::create('/');

        return new GoogleProvider(
            $request,
            'test-client-id',
            'test-client-secret',
            'http://localhost/callback'
        );
    }

    /**
     * Create a mock JWT token for testing.
     */
    protected function createMockJwtToken()
    {
        $header = ['typ' => 'JWT', 'alg' => 'RS256', 'kid' => 'test-key-id'];
        $payload = [
            'iss' => 'https://accounts.google.com',
            'sub' => '123456789012345678901',
            'aud' => 'test-client-id',
            'email' => 'testuser@gmail.com',
            'email_verified' => true,
            'name' => 'Test User',
            'picture' => 'https://lh3.googleusercontent.com/photo.jpg',
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        return $this->base64UrlEncode(json_encode($header)).
            '.'.
            $this->base64UrlEncode(json_encode($payload)).
            '.'.
            $this->base64UrlEncode('mock-signature');
    }

    /**
     * Mock JWKS response for testing JWT verification.
     */
    protected function mockJwksResponse($provider)
    {
        $httpClient = m::mock(Client::class);
        $provider->setHttpClient($httpClient);

        $jwksResponse = m::mock(ResponseInterface::class);
        $jwksStream = m::mock(StreamInterface::class);

        $mockJwks = [
            'keys' => [
                [
                    'kid' => 'test-key-id',
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'n' => 'mock-n-value',
                    'e' => 'AQAB',
                ],
            ],
        ];

        $httpClient->shouldReceive('get')
            ->with('https://www.googleapis.com/oauth2/v3/certs')
            ->once()
            ->andReturn($jwksResponse);

        $jwksResponse->shouldReceive('getBody')->once()->andReturn($jwksStream);
        $jwksStream->shouldReceive('__toString')->once()->andReturn(json_encode($mockJwks));
    }

    /**
     * Create an invalid JWT token for testing.
     */
    protected function createInvalidJwtToken(array $overrides)
    {
        $header = array_merge(
            ['typ' => 'JWT', 'alg' => 'RS256', 'kid' => 'test-key-id'],
            $overrides['header'] ?? []
        );

        $payload = array_merge([
            'iss' => 'https://accounts.google.com',
            'sub' => '123456789',
            'aud' => 'test-client-id',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'iat' => time(),
            'exp' => time() + 3600,
        ], $overrides['payload'] ?? []);

        $header = array_filter($header, function ($value) {
            return $value !== null;
        });

        return $this->base64UrlEncode(json_encode($header)).'.'.
               $this->base64UrlEncode(json_encode($payload)).'.'.
               $this->base64UrlEncode('mock-signature');
    }

    /**
     * Base64URL encode data.
     */
    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
