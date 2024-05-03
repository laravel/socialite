<?php

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\User as UserContract;
use Laravel\Socialite\Two\SlackOpenIdProvider;
use Laravel\Socialite\Two\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class SlackOpenIdProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function test_response()
    {
        $user = $this->fromResponse([
            'sub' => 'U1Q2W3E4R5T',
            'given_name' => 'Maarten',
            'picture' => 'https://secure.gravatar.com/avatar/qwerty-123.jpg?s=512',
            'name' => 'Maarten Paauw',
            'family_name' => 'Paauw',
            'email' => 'maarten.paauw@example.com',
            'https://slack.com/team_id' => 'T0P9O8I7U6Y',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('U1Q2W3E4R5T', $user->getId());
        $this->assertNull($user->getNickname());
        $this->assertSame('Maarten Paauw', $user->getName());
        $this->assertSame('maarten.paauw@example.com', $user->getEmail());
        $this->assertSame('https://secure.gravatar.com/avatar/qwerty-123.jpg?s=512', $user->getAvatar());

        $this->assertSame([
            'id' => 'U1Q2W3E4R5T',
            'nickname' => null,
            'name' => 'Maarten Paauw',
            'email' => 'maarten.paauw@example.com',
            'avatar' => 'https://secure.gravatar.com/avatar/qwerty-123.jpg?s=512',
            'organization_id' => 'T0P9O8I7U6Y',
        ], $user->attributes);
    }

    public function test_missing_email_and_avatar()
    {
        $user = $this->fromResponse([
            'sub' => 'U1Q2W3E4R5T',
            'given_name' => 'Maarten',
            'name' => 'Maarten Paauw',
            'family_name' => 'Paauw',
            'https://slack.com/team_id' => 'T0P9O8I7U6Y',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('U1Q2W3E4R5T', $user->getId());
        $this->assertNull($user->getNickname());
        $this->assertSame('Maarten Paauw', $user->getName());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getAvatar());

        $this->assertSame([
            'id' => 'U1Q2W3E4R5T',
            'nickname' => null,
            'name' => 'Maarten Paauw',
            'email' => null,
            'avatar' => null,
            'organization_id' => 'T0P9O8I7U6Y',
        ], $user->attributes);
    }

    protected function fromResponse(array $response): UserContract
    {
        $request = m::mock(Request::class);
        $request->allows('input')->with('code')->andReturns('fake-code');

        $stream = m::mock(StreamInterface::class);
        $stream->allows('__toString')->andReturns(json_encode(['access_token' => 'fake-token']));

        $accessTokenResponse = m::mock(ResponseInterface::class);
        $accessTokenResponse->allows('getBody')->andReturns($stream);

        $basicProfileStream = m::mock(StreamInterface::class);
        $basicProfileStream->allows('__toString')->andReturns(json_encode($response));

        $basicProfileResponse = m::mock(ResponseInterface::class);
        $basicProfileResponse->allows('getBody')->andReturns($basicProfileStream);

        $guzzle = m::mock(Client::class);
        $guzzle->expects('post')->andReturns($accessTokenResponse);
        $guzzle->allows('get')->with('https://slack.com/api/openid.connect.userInfo', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer fake-token',
            ],
        ])->andReturns($basicProfileResponse);

        $provider = new SlackOpenIdProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->stateless();
        $provider->setHttpClient($guzzle);

        return $provider->user();
    }
}
