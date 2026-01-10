<?php

namespace Laravel\Socialite\Tests;

use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\SocialiteServiceProvider;
use Laravel\Socialite\Testing\FakeProvider;
use Laravel\Socialite\Testing\SocialiteFake;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as OAuth2User;
use Orchestra\Testbench\TestCase;

class SocialiteFakeTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [SocialiteServiceProvider::class];
    }

    protected function tearDown(): void
    {
        Socialite::clearResolvedInstances();

        parent::tearDown();
    }

    public function test_it_can_fake_a_driver_with_a_user()
    {
        $user = (new OAuth2User)->map([
            'id' => '123',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Socialite::fake('github', $user);

        $this->assertInstanceOf(SocialiteFake::class, $this->app->make(Factory::class));
        $this->assertInstanceOf(FakeProvider::class, Socialite::driver('github'));

        $retrievedUser = Socialite::driver('github')->user();

        $this->assertSame('123', $retrievedUser->getId());
        $this->assertSame('Test User', $retrievedUser->getName());
        $this->assertSame('test@example.com', $retrievedUser->getEmail());
    }

    public function test_it_can_fake_a_driver_with_a_closure()
    {
        Socialite::fake('github', function () {
            return (new OAuth2User)->map([
                'id' => '456',
                'name' => 'Closure User',
                'email' => 'closure@example.com',
            ]);
        });

        $user = Socialite::driver('github')->user();

        $this->assertSame('456', $user->getId());
        $this->assertSame('Closure User', $user->getName());
    }

    public function test_it_can_fake_multiple_drivers()
    {
        Socialite::fake('github', (new OAuth2User)->map(['id' => 'github-123']));
        Socialite::fake('google', (new OAuth2User)->map(['id' => 'google-456']));

        $this->assertSame('github-123', Socialite::driver('github')->user()->getId());
        $this->assertSame('google-456', Socialite::driver('google')->user()->getId());
    }

    public function test_it_returns_fake_redirect_response()
    {
        Socialite::fake('github', (new OAuth2User)->map(['id' => '123']));

        $response = Socialite::driver('github')->redirect();

        $this->assertSame('https://socialite.fake/github/authorize', $response->getTargetUrl());
    }

    public function test_it_forwards_calls_to_the_real_provider_methods()
    {
        $this->app['config']->set('services.github', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'redirect' => 'http://localhost/callback',
        ]);

        Socialite::fake('github', (new OAuth2User)->map(['id' => '123']));

        $provider = Socialite::driver('github');

        // Verify that methods are forwarded to the real provider
        $provider->stateless();
        $provider->scopes(['user', 'repo']);
        $provider->setScopes(['user:email']);
        $provider->redirectUrl('http://example.com/callback');
        $provider->with(['custom' => 'param']);
        $provider->enablePKCE();

        // Verify that the fake user is returned despite calling other methods
        $user = $provider->user();

        $this->assertSame('123', $user->getId());
    }

    public function test_it_preserves_decorator_pattern_when_chaining_methods()
    {
        $this->app['config']->set('services.github', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'redirect' => 'http://localhost/callback',
        ]);

        Socialite::fake('github', (new OAuth2User)->map(['id' => '123']));

        $provider = Socialite::driver('github');
        $this->assertInstanceOf(FakeProvider::class, $provider);

        $chainedProvider = $provider->stateless()
            ->scopes(['user', 'repo'])
            ->setScopes(['user:email'])
            ->redirectUrl('http://example.com/callback')
            ->with(['custom' => 'param'])
            ->enablePKCE();

        $this->assertInstanceOf(FakeProvider::class, $chainedProvider, 'FakeProvider should be returned, not the real provider');
        $this->assertSame($provider, $chainedProvider);

        $user = $chainedProvider->user();
        $this->assertSame('123', $user->getId());
    }

    public function test_it_returns_real_driver_when_not_faked()
    {
        $this->app['config']->set('services.github', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'redirect' => 'http://localhost/callback',
        ]);

        $this->app['config']->set('services.google', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'redirect' => 'http://localhost/callback',
        ]);

        // Fake only github
        Socialite::fake('github', (new OAuth2User)->map(['id' => '123']));

        // Github should return the fake provider
        $this->assertInstanceOf(FakeProvider::class, Socialite::driver('github'));

        // Google should return the real provider since it wasn't faked
        $this->assertInstanceOf(GoogleProvider::class, Socialite::driver('google'));
    }
}
