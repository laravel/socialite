<?php

namespace Laravel\Socialite\Tests;

use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Exceptions\DriverMissingConfigurationException;
use Laravel\Socialite\SocialiteServiceProvider;
use Laravel\Socialite\Two\GithubProvider;
use Orchestra\Testbench\TestCase;

class SocialiteManagerTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('services.github', [
            'client_id' => 'github-client-id',
            'client_secret' => 'github-client-secret',
            'redirect' => 'http://your-callback-url',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [SocialiteServiceProvider::class];
    }

    public function test_relative_redirect_uses_configured_origin_for_authorization_and_token_exchange()
    {
        $this->app['config']->set('app.url', 'https://app.example.com/base/');
        $this->app['config']->set('services.github.redirect', '/auth/github/callback?flow=login');
        $this->app->instance('request', \Illuminate\Http\Request::create('http://evil.example.com/start'));
        $this->app['url']->setRequest($this->app['request']);
        $provider = $this->app->make(Factory::class)->driver('github')->stateless();
        parse_str(parse_url($provider->redirect()->getTargetUrl(), PHP_URL_QUERY), $query);
        $expected = 'https://app.example.com/base/auth/github/callback?flow=login';
        $this->assertSame($expected, $query['redirect_uri']);

        $history = [];
        $stack = \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], '{"access_token":"token"}'),
        ]));
        $stack->push(\GuzzleHttp\Middleware::history($history));
        $provider->setHttpClient(new \GuzzleHttp\Client(['handler' => $stack]));
        $provider->getAccessTokenResponse('code');
        parse_str((string) $history[0]['request']->getBody(), $form);
        $this->assertSame($expected, $form['redirect_uri']);
    }

    public function test_network_path_redirect_cannot_override_configured_origin()
    {
        $this->app['config']->set('app.url', 'https://app.example.com');
        $this->app['config']->set('services.github.redirect', fn () => '//evil.example.com/callback');
        $provider = $this->app->make(Factory::class)->driver('github')->stateless();
        parse_str(parse_url($provider->redirect()->getTargetUrl(), PHP_URL_QUERY), $query);
        $this->assertSame('https://app.example.com/evil.example.com/callback', $query['redirect_uri']);
    }

    public function test_absolute_redirect_is_preserved()
    {
        $this->app['config']->set('services.github.redirect', 'https://auth.example.com:8443/callback?q=1');
        $provider = $this->app->make(Factory::class)->driver('github')->stateless();
        parse_str(parse_url($provider->redirect()->getTargetUrl(), PHP_URL_QUERY), $query);
        $this->assertSame('https://auth.example.com:8443/callback?q=1', $query['redirect_uri']);
    }

    public function test_relative_redirect_rejects_missing_trusted_origin()
    {
        $this->app['config']->set('app.url', null);
        $this->app['config']->set('services.github.redirect', '/callback');
        $this->expectException(\InvalidArgumentException::class);
        $this->app->make(Factory::class)->driver('github');
    }

    public function test_oauth_one_relative_callback_uses_configured_origin()
    {
        $this->app['config']->set('app.url', 'https://app.example.com');
        $config = $this->app->make(Factory::class)->formatConfig([
            'client_id' => 'id', 'client_secret' => 'secret', 'redirect' => '/callback',
        ]);
        $this->assertSame('https://app.example.com/callback', $config['callback_uri']);
    }

    public function test_it_can_instantiate_the_github_driver()
    {
        $factory = $this->app->make(Factory::class);

        $provider = $factory->driver('github');

        $this->assertInstanceOf(GithubProvider::class, $provider);
    }

    public function test_it_can_instantiate_the_github_driver_with_scopes_from_config_array()
    {
        $factory = $this->app->make(Factory::class);
        $this->app['config']->set('services.github', [
            'client_id' => 'github-client-id',
            'client_secret' => 'github-client-secret',
            'redirect' => 'http://your-callback-url',
            'scopes' => ['user:email', 'read:user'],
        ]);
        $provider = $factory->driver('github');
        $this->assertSame(['user:email', 'read:user'], $provider->getScopes());
    }

    public function test_it_can_instantiate_the_github_driver_with_scopes_without_array_from_config()
    {
        $factory = $this->app->make(Factory::class);
        $provider = $factory->driver('github');
        $this->assertSame(['user:email'], $provider->getScopes());
    }

    public function test_it_can_instantiate_the_github_driver_with_scopes_from_config_array_merged_by_programmatic_scopes_using_scopes_method()
    {
        $factory = $this->app->make(Factory::class);
        $this->app['config']->set('services.github', [
            'client_id' => 'github-client-id',
            'client_secret' => 'github-client-secret',
            'redirect' => 'http://your-callback-url',
            'scopes' => ['user:email'],
        ]);
        $provider = $factory->driver('github')->scopes(['read:user']);
        $this->assertSame(['user:email', 'read:user'], $provider->getScopes());
    }

    public function test_it_can_instantiate_the_github_driver_with_scopes_from_config_array_overwritten_by_programmatic_scopes_using_set_scopes_method()
    {
        $factory = $this->app->make(Factory::class);
        $this->app['config']->set('services.github', [
            'client_id' => 'github-client-id',
            'client_secret' => 'github-client-secret',
            'redirect' => 'http://your-callback-url',
            'scopes' => ['user:email'],
        ]);
        $provider = $factory->driver('github')->setScopes(['read:user']);
        $this->assertSame(['read:user'], $provider->getScopes());
    }

    public function test_it_throws_exception_when_client_secret_is_missing()
    {
        $this->expectException(DriverMissingConfigurationException::class);
        $this->expectExceptionMessage('Missing required configuration keys [client_secret] for [Laravel\Socialite\Two\GithubProvider] OAuth provider.');

        $factory = $this->app->make(Factory::class);

        $this->app['config']->set('services.github', [
            'client_id' => 'github-client-id',
            'redirect' => 'http://your-callback-url',
        ]);

        $factory->driver('github');
    }

    public function test_it_throws_exception_when_client_id_is_missing()
    {
        $this->expectException(DriverMissingConfigurationException::class);
        $this->expectExceptionMessage('Missing required configuration keys [client_id] for [Laravel\Socialite\Two\GithubProvider] OAuth provider.');

        $factory = $this->app->make(Factory::class);

        $this->app['config']->set('services.github', [
            'client_secret' => 'github-client-secret',
            'redirect' => 'http://your-callback-url',
        ]);

        $factory->driver('github');
    }

    public function test_it_throws_exception_when_redirect_is_missing()
    {
        $this->expectException(DriverMissingConfigurationException::class);
        $this->expectExceptionMessage('Missing required configuration keys [redirect] for [Laravel\Socialite\Two\GithubProvider] OAuth provider.');

        $factory = $this->app->make(Factory::class);

        $this->app['config']->set('services.github', [
            'client_id' => 'github-client-id',
            'client_secret' => 'github-client-secret',
        ]);

        $factory->driver('github');
    }

    public function test_it_throws_exception_when_configuration_is_completely_missing()
    {
        $this->expectException(DriverMissingConfigurationException::class);
        $this->expectExceptionMessage('Missing required configuration keys [client_id, client_secret, redirect] for [Laravel\Socialite\Two\GithubProvider] OAuth provider.');

        $factory = $this->app->make(Factory::class);

        $this->app['config']->set('services.github', null);

        $factory->driver('github');
    }
}
