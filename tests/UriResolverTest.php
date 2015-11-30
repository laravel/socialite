<?php

use Laravel\Socialite\UriResolver;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Routing\RouteCollection;

class UriResolverTest extends PHPUnit_Framework_TestCase
{
    public function testProperUrlIsGeneratedWhenNamedRouteIsPresent()
    {
        $uriResolver = $this->generateUrlResolver([
            new Route(['GET'], '/callback', ['as' => 'named:callback']),
            new Route(['GET'], '/other_callback', ['as' => 'named:other_callback']),
        ]);

        $this->assertEquals('http://www.foo.com/callback', $uriResolver->resolve('named:callback'));
    }

    public function testProperUrlIsGeneratedWhenUrlIsPassed()
    {
        $uriResolver = $this->generateUrlResolver([
            new Route(['GET'], '/callback', ['as' => 'named:callback']),
            new Route(['GET'], '/other_callback', ['as' => 'named:other_callback']),
        ]);

        $this->assertEquals('http://www.no-foo.com/callback', $uriResolver->resolve('http://www.no-foo.com/callback'));
    }

    public function testProperUrlIsGeneratedWhenPathIsPassed()
    {
        $uriResolver = $this->generateUrlResolver([
            new Route(['GET'], '/callback', []),
            new Route(['GET'], '/other_callback', []),
        ]);

        $this->assertEquals('http://www.foo.com/callback', $uriResolver->resolve('callback'));
        $this->assertEquals('http://www.foo.com/absent_callback', $uriResolver->resolve('absent_callback'));
    }

    protected function generateUrlResolver(array $routes, Request $request = null)
    {
        if ($request == null) {
            $request = Request::create('http://www.foo.com');
        }

        $routeCollection = new RouteCollection();
        foreach ($routes as $route) {
            $routeCollection->add($route);
        }

        return new UriResolver(new UrlGenerator($routeCollection, $request));
    }
}
