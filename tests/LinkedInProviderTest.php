<?php

namespace Laravel\Socialite\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\LinkedInProvider;
use Laravel\Socialite\Two\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class LinkedInProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function test_it_can_map_a_user_without_an_email_address()
    {
        $request = m::mock(Request::class);
        $request->allows('input')->with('code')->andReturns('fake-code');

        $stream = m::mock(StreamInterface::class);
        $stream->allows('__toString')->andReturns(json_encode(['access_token' => 'fake-token']));

        $accessTokenResponse = m::mock(ResponseInterface::class);
        $accessTokenResponse->allows('getBody')->andReturns($stream);

        $basicProfileStream = m::mock(StreamInterface::class);
        $basicProfileStream->allows('__toString')->andReturns(json_encode(['id' => $userId = 1]));

        $basicProfileResponse = m::mock(ResponseInterface::class);
        $basicProfileResponse->allows('getBody')->andReturns($basicProfileStream);

        $emailAddressStream = m::mock(StreamInterface::class);
        $emailAddressStream->allows('__toString')->andReturns(json_encode(['elements' => []]));

        // Make sure email address response contains no values.
        $emailAddressResponse = m::mock(ResponseInterface::class);
        $emailAddressResponse->allows('getBody')->andReturns($emailAddressStream);

        $guzzle = m::mock(Client::class);
        $guzzle->expects('post')->andReturns($accessTokenResponse);
        $guzzle->allows('get')->with('https://api.linkedin.com/v2/me', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer fake-token',
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            RequestOptions::QUERY => [
                'projection' => '(id,firstName,lastName,profilePicture(displayImage~:playableStreams),vanityName)',
            ],
        ])->andReturns($basicProfileResponse);
        $guzzle->allows('get')->with('https://api.linkedin.com/v2/emailAddress', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer fake-token',
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            RequestOptions::QUERY => [
                'q' => 'members',
                'projection' => '(elements*(handle~))',
            ],
        ])->andReturns($emailAddressResponse);

        $provider = new LinkedInProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->stateless();
        $provider->setHttpClient($guzzle);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($userId, $user->getId());
        $this->assertNull($user->getEmail());
    }

    public function test_it_can_map_a_user_when_the_still_image_key_is_missing()
    {
        $request = m::mock(Request::class);
        $request->allows('input')->with('code')->andReturns('fake-code');

        $stream = m::mock(StreamInterface::class);
        $stream->allows('__toString')->andReturns(json_encode(['access_token' => 'fake-token']));

        $accessTokenResponse = m::mock(ResponseInterface::class);
        $accessTokenResponse->allows('getBody')->andReturns($stream);

        $basicProfileStream = m::mock(StreamInterface::class);
        $basicProfileStream->allows('__toString')->andReturns(json_encode([
            'id' => $userId = 1,
            'profilePicture' => [
                'displayImage~' => [
                    'elements' => [
                        [
                            'identifiers' => [
                                ['identifier' => 'https://media.licdn.com/avatar.jpg'],
                            ],
                            'data' => [],
                        ],
                    ],
                ],
            ],
        ]));

        $basicProfileResponse = m::mock(ResponseInterface::class);
        $basicProfileResponse->allows('getBody')->andReturns($basicProfileStream);

        $emailAddressStream = m::mock(StreamInterface::class);
        $emailAddressStream->allows('__toString')->andReturns(json_encode(['elements' => []]));

        $emailAddressResponse = m::mock(ResponseInterface::class);
        $emailAddressResponse->allows('getBody')->andReturns($emailAddressStream);

        $guzzle = m::mock(Client::class);
        $guzzle->expects('post')->andReturns($accessTokenResponse);
        $guzzle->allows('get')->with('https://api.linkedin.com/v2/me', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer fake-token',
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            RequestOptions::QUERY => [
                'projection' => '(id,firstName,lastName,profilePicture(displayImage~:playableStreams),vanityName)',
            ],
        ])->andReturns($basicProfileResponse);
        $guzzle->allows('get')->with('https://api.linkedin.com/v2/emailAddress', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer fake-token',
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            RequestOptions::QUERY => [
                'q' => 'members',
                'projection' => '(elements*(handle~))',
            ],
        ])->andReturns($emailAddressResponse);

        $provider = new LinkedInProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->stateless();
        $provider->setHttpClient($guzzle);

        // LinkedIn omits the StillImage key for some accounts, and Laravel turns
        // the resulting "Undefined array key" warning into an exception.
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $user = $provider->user();
        } finally {
            restore_error_handler();
        }

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($userId, $user->getId());
        $this->assertNull($user->getAvatar());
    }
}
