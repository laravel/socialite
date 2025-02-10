<?php

namespace Laravel\Socialite;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class DiscordServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Socialite::extend('discord', function (Application $app) {
            $config = $app->make('config')->get('services.discord');

            $redirect = value(Arr::get($config, 'redirect'));

            return new DiscordProvider(
                $app->make('request'),
                $config['client_id'],
                $config['client_secret'],
                Str::startsWith($redirect, '/') ? $app->make('url')->to($redirect) : $redirect,
                Arr::get($config, 'guzzle', [])
            );
        });
    }
}