<?php

namespace Laravel\Socialite\Tests;

use Laravel\Socialite\Two\AppleProvider;
use Mockery as m;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\User;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Laravel\Socialite\Two\LinkedInProvider;

class AppleProviderTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();

        m::close();
    }

    public function test_get_apple_token()
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('input')->with('code')->andReturn('fake-code');

        $accessTokenResponse = m::mock(ResponseInterface::class);
        $accessTokenResponse->shouldReceive('getBody')->andReturn(json_encode(['id_token' => "eyJraWQiOiJBSURPUEDSDwiYWxnIjoiUlMyNTYifQ.eyJpc3MiOiJodHRwczpcL1wvYXBwbGVpZC5hcHBsZS5jb20iLCJhdWQiOiJ3d3cuZXhhbXBsZS5jb20iLCJleHAiOjE1NjE0OTA2MTUsImlhdCI6MTU2MTUzMzU4OCwic3ViIjoiMDAwMTMyLmMzTWlPaUpvZEhSd2N6b3ZMMkZ3Y0d4bGFXUWFzZHNhLjg5MTQiLCJhdF9oYXNoIjoiQnBiVmVmTm5waVBUY1BzcWt3VEppZyIsImVtYWlsIjoiZXhhbXBsZUBwcml2YXRlcmVsYXkuYXBwbGVpZC5jb20ifQ==.SyCF8jT50FHALit-u9H_TyzPikirYnDq1RiDT3ennHQrLOAcRE4bDmVM1qlG2cfHPH5OtpyQZIjGi_r9v7ZoN2EfyDGlg08yEWGwwCNlrCkcHcA9gjNN2RYmT4Yt3toRLgnwSDyzHOP6FS7I1kzwcdZmJTuGrYPThxe80F6rQABUWUBDAl2KgP7ujt1j8H3LrfV0r3RKTHA7azWWu9rVAFrx1_IeRk-ASDW0OPrqDJoF8YdZF1Da4-br-gTOt_LJhZFhuPh1WDgZj6AAcytTrSL4AhW2BrN_U0bMw88nw7k9OZbcbDNb-j3hEAkQdvZYEBHIRtEMxrzTAgs7oxbtg"]));

        $guzzle = m::mock(Client::class);
        $guzzle->shouldReceive('post')->once()->andReturn($accessTokenResponse);

        $provider = new AppleProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->stateless();
        $provider->setHttpClient($guzzle);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);

        $this->assertEquals('example@privaterelay.appleid.com', $user->getEmail()); //TODO After the apple update
        $this->assertEquals('000132.c3MiOiJodHRwczovL2FwcGxlaWQasdsa.8914' , $user->getId());
    }
}
