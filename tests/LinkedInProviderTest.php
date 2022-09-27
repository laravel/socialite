<?php

namespace Laravel\Socialite\Tests;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\LinkedInProvider;
use Laravel\Socialite\Two\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class LinkedInProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function testItCanMapAUserWithoutAnEmailAddress(): void
    {
        $request = m::mock(Request::class);
        $request->allows('input')->with('code')->andReturns('fake-code');

        $accessTokenResponse = m::mock(ResponseInterface::class);
        $accessTokenResponse->allows('getBody')->andReturns(json_encode([
            'access_token' => 'fake-token',
            'refresh_token' => 'fake-refresh-token',
            'expires_in' => 3600,
        ]));

        $basicProfileResponse = m::mock(ResponseInterface::class);
        $basicProfileResponse->allows('getBody')->andReturns(json_encode(['id' => $userId = 1]));

        // Make sure email address response contains no values.
        $emailAddressResponse = m::mock(ResponseInterface::class);
        $emailAddressResponse->allows('getBody')->andReturns(json_encode(['elements' => []]));

        $guzzle = m::mock(Client::class);
        $guzzle->expects('post')->andReturns($accessTokenResponse);
        $guzzle->allows('get')->with('https://api.linkedin.com/v2/me', [
            'headers' => [
                'Authorization' => 'Bearer fake-token',
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            'query' => [
                'projection' => '(id,firstName,lastName,profilePicture(displayImage~:playableStreams))',
            ],
        ])->andReturns($basicProfileResponse);
        $guzzle->allows('get')->with('https://api.linkedin.com/v2/emailAddress', [
            'headers' => [
                'Authorization' => 'Bearer fake-token',
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            'query' => [
                'q' => 'members',
                'projection' => '(elements*(handle~))',
            ],
        ])->andReturns($emailAddressResponse);

        $provider = new LinkedInProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->stateless();
        $provider->setHttpClient($guzzle);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userId, $user->getId());
        $this->assertNull($user->getEmail());
    }
}
