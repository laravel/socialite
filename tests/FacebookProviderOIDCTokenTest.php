<?php

namespace Laravel\Socialite\Tests;

use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Laravel\Socialite\Tests\Fixtures\FacebookOIDCTestProviderStub;
use Laravel\Socialite\Two\User;
use PHPUnit\Framework\TestCase;

class FacebookProviderOIDCTokenTest extends TestCase
{
    public function test_it_validates_expected_nonce_for_facebook_oidc_tokens()
    {
        $provider = $this->getProvider();

        $user = $provider->userFromToken(
            $this->createOidcToken(['nonce' => 'expected-nonce']),
            'expected-nonce'
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('123456789', $user->getId());
        $this->assertSame('Taylor Otwell', $user->getName());
        $this->assertSame('taylor@example.com', $user->getEmail());
    }

    public function test_it_validates_expected_nonce_from_custom_parameters()
    {
        $provider = $this->getProvider();

        $user = $provider
            ->with(['nonce' => 'expected-nonce'])
            ->userFromToken($this->createOidcToken(['nonce' => 'expected-nonce']));

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('123456789', $user->getId());
    }

    public function test_it_rejects_facebook_oidc_tokens_with_an_incorrect_nonce()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token has incorrect nonce.');

        $provider = $this->getProvider();

        $provider->userFromToken(
            $this->createOidcToken(['nonce' => 'unexpected-nonce']),
            'expected-nonce'
        );
    }

    public function test_it_rejects_facebook_oidc_tokens_without_an_expected_nonce()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token has incorrect nonce.');

        $provider = $this->getProvider();

        $provider->userFromToken($this->createOidcToken(['nonce' => 'expected-nonce']));
    }

    public function test_it_rejects_facebook_oidc_tokens_without_a_nonce_when_one_is_expected()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token has incorrect nonce.');

        $provider = $this->getProvider();

        $provider->userFromToken(
            $this->createOidcToken(['nonce' => null]),
            'expected-nonce'
        );
    }

    protected function getProvider()
    {
        return new FacebookOIDCTestProviderStub(
            Request::create('/'),
            'client_id',
            'client_secret',
            'http://localhost/callback'
        );
    }

    protected function createOidcToken(array $overrides = [])
    {
        $payload = array_merge([
            'iss' => 'https://www.facebook.com',
            'sub' => '123456789',
            'aud' => 'client_id',
            'name' => 'Taylor Otwell',
            'email' => 'taylor@example.com',
            'iat' => time(),
            'exp' => time() + 3600,
        ], $overrides);

        $payload = array_filter($payload, function ($value) {
            return $value !== null;
        });

        return JWT::encode($payload, FacebookOIDCTestProviderStub::TEST_SECRET, 'HS256', 'test-key-id');
    }
}
