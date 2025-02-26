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
