<?php

namespace Laravel\Socialite\Tests;

use function GuzzleHttp\Psr7\parse_query;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\SocialiteManager;
use Laravel\Socialite\Two\FacebookProvider;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class RouteGenerationTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function testValidRedirectUriIsPassedThrough()
    {
        $driver = $this->driver([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect' => 'https://example.com/callback',
        ]);

        $this->assertEquals('https://example.com/callback', $this->redirectUrlFromResponse($driver->redirect()));
    }

    public function testRedirectUriCanBeGeneratedFromUri()
    {
        $url = m::mock(UrlGenerator::class);
        $url->shouldReceive('to')->with('/callback')->andReturn('https://example.com/callback');

        $driver = $this->driver([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect' => '/callback',
        ], $url);

        $this->assertEquals('https://example.com/callback', $this->redirectUrlFromResponse($driver->redirect()));
    }

    public function testRedirectUriCanBeGeneratedFromAction()
    {
        $url = m::mock(UrlGenerator::class);
        $url->shouldReceive('action')->with('callback', [0, 21])->andReturn('https://example.com/callback');

        $driver = $this->driver([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect' => ['action' => 'callback', 'parameters' => [0, 21]],
        ], $url);

        $this->assertEquals('https://example.com/callback', $this->redirectUrlFromResponse($driver->redirect()));
    }

    public function testRedirectUriCanBeGeneratedFromRoute()
    {
        $url = m::mock(UrlGenerator::class);
        $url->shouldReceive('route')->with('callback', [0, 21])->andReturn('https://example.com/callback');

        $driver = $this->driver([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect' => ['route' => 'callback', 'parameters' => [0, 21]],
        ], $url);

        $this->assertEquals('https://example.com/callback', $this->redirectUrlFromResponse($driver->redirect()));
    }

    public function testRedirectUriCannotBeGeneratedFromInvalidArray()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->driver([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect' => [],
        ]);
    }

    protected function driver(array $driverConfig, ?UrlGenerator $url = null): FacebookProvider
    {
        $config = m::mock(Repository::class, \ArrayAccess::class);
        $config->shouldReceive('offsetGet')->with('services.facebook')->andReturn($driverConfig);

        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->shouldIgnoreMissing();

        $container = m::mock(Container::class);
        $container->shouldReceive('make')->with('config')->andReturn($config);
        $container->shouldReceive('make')->with('request')->andReturn($request);
        $container->shouldReceive('make')->with('url')->andReturn($url);

        return (new SocialiteManager($container))->driver('facebook');
    }

    protected function redirectUrlFromResponse(RedirectResponse $response): string
    {
        return parse_query(Str::after($response->getTargetUrl(), '?'))['redirect_uri'];
    }
}
