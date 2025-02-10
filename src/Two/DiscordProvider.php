<?php

namespace Laravel\Socialite\Two;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class DiscordProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritDoc}
     */
    protected $scopes = [
        'email',
        'identify',
    ];

    /**
     * {@inheritDoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * Create a new bot redirect.
     *
     * @return \Laravel\Socialite\Two\BotRedirectBuilder
     */
    public function bot()
    {
        return new BotRedirectBuilder($this->clientId);
    }

    /**
     * {@inheritDoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://discord.com/api/oauth2/authorize', $state);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenUrl()
    {
        return 'https://discord.com/api/oauth2/token';
    }

    /**
     * {@inheritDoc}
     */
    protected function getUserByToken($token)
    {
        $userUrl = 'https://discord.com/api/users/@me';

        $response = $this->getHttpClient()->get($userUrl, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritDoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['id'],
            'name' => $user['username'],
            'email' => $user['email'],
            'avatar' => sprintf('https://cdn.discordapp.com/avatars/%s/%s.png', $user['id'], $user['avatar']),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUrl,
        ];
    }
}