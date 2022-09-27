<?php

namespace Laravel\Socialite\Tests;

use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\SocialiteServiceProvider;
use Laravel\Socialite\Two\GithubProvider;
use Orchestra\Testbench\TestCase;

class SocialiteManagerTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('services.github', [
            'client_id' => 'github-client-id',
            'client_secret' => 'github-client-secret',
            'redirect' => 'http://your-callback-url',
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [SocialiteServiceProvider::class];
    }

    public function testItCanInstantiateTheGithubDriver(): void
    {
        $factory = $this->app->make(Factory::class);

        $provider = $factory->driver('github');

        $this->assertInstanceOf(GithubProvider::class, $provider);
    }
}
