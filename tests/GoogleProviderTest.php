<?php

namespace Laravel\Socialite\Tests;

use Illuminate\Http\Request;
use Laravel\Socialite\Jwt\GoogleProvider;
use Laravel\Socialite\Jwt\InvalidJwtException;
use Laravel\Socialite\Jwt\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class GoogleProviderTest extends TestCase
{
    public function testRedirectThrowsException()
    {
        $request = new Request();
        $provider = new GoogleProvider($request, 'dummy-client-id');

        $this->expectException(\RuntimeException::class);

        $provider->redirect();
    }

    public function testUserWithNullCredentialThrowsException()
    {
        $request = new Request();
        $request->replace(['credential' => null]);

        $provider = new GoogleProvider($request, 'dummy-client-id');

        $this->expectException(InvalidJwtException::class);
        $this->expectExceptionMessage('Empty JWT payload');

        $provider->user();
    }

    public function testVerifyJwtSignatureWithInvalidJwt()
    {
        $jwt = $this->generateDummyJwt();

        $request = new Request();
        $request->replace(['credential' => $jwt]);

        $provider = m::mock(GoogleProvider::class, [$request, 'dummy-client-id'])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $provider->shouldReceive('fetchCertificates')
            ->once()
            ->andReturn(['dummyKeyId' => 'dummyPublicKey']);

        $provider->shouldReceive('verifySignature')
            ->once()
            ->andReturn(false);

        $this->expectException(InvalidJwtException::class);
        $this->expectExceptionMessage('Invalid JWT signature');

        $provider->user();
    }

    public function testUserWithValidJwtReturnsUserInstance()
    {
        $jwt = $this->generateDummyJwt();

        $request = new Request();
        $request->replace(['credential' => $jwt]);

        $provider = m::mock(GoogleProvider::class, [$request, 'dummy-client-id'])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $provider->shouldReceive('fetchCertificates')
            ->once()
            ->andReturn(['dummyKeyId' => 'dummyPublicKey']);

        $provider->shouldReceive('verifySignature')
            ->once()
            ->andReturn(true);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
    }

    private function generateDummyJwt(): string
    {
        $header = ['alg' => 'RS256', 'kid' => 'dummyKeyId'];
        $payload = ['sub' => '1234567890', 'name' => 'John Doe', 'iss' => 'accounts.google.com', 'aud' => 'dummy-client-id', 'exp' => time() + 3600, 'iat' => time(), 'email_verified' => true, 'email' => 'john@doe.com'];
        $signature = 'dummySignature';

        $jwtParts = [
            base64_encode(json_encode($header)),
            base64_encode(json_encode($payload)),
            base64_encode($signature)
        ];

        return implode('.', $jwtParts);
    }
}
