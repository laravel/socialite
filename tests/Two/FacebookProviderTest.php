<?php

use Illuminate\Http\Request;
use Laravel\Socialite\Two\FacebookProvider;

class FacebookProviderTest extends PHPUnit_Framework_TestCase
{
    public function createGetUserUrlProvider()
    {
        return [
            ['foobar', 'token', [], 'fb/v/me?access_token=token&appsecret_proof=foobar&fields=name,email'],
            ['baz', 'token2', ['locale' => 'ja'], 'fb/v/me?access_token=token2&appsecret_proof=baz&fields=name,email&locale=ja'],
            ['baz', 'token3', ['locale' => 'ja', 'return_ssl_resources' => 'false'], 'fb/v/me?access_token=token3&appsecret_proof=baz&fields=name,email&locale=ja&return_ssl_resources=false'],
        ];
    }

    /**
     * @dataProvider createGetUserUrlProvider
     */
    public function testCreateGetUserUrl($appSecretProof, $token, $params, $expect)
    {
        $provider = new FacebookProviderWrapper();
        $actual = $provider->with($params)->callCreateGetUserUrl($token, $appSecretProof);
        $this->assertSame($expect, $actual);
    }
}

class FacebookProviderWrapper extends FacebookProvider
{
    public function __construct()
    {
        $request = Request::create('foo');
        parent::__construct($request, 'foo', 'bar', 'baz');

        $this->graphUrl = 'fb';
        $this->version = 'v';
        $this->fields = ['name', 'email'];
    }

    public function callCreateGetUserUrl($token, $appSecretProof)
    {
        return $this->createGetUserUrl($token, $appSecretProof);
    }
}
