<?php

namespace Laravel\Socialite\Two;

use Illuminate\Support\Arr;

class TwitterProvider extends AbstractProvider
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected array $scopes = ['users.read', 'tweet.read'];

    /**
     * Indicates if PKCE should be used.
     *
     * @var bool
     */
    protected bool $usesPKCE = true;

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected string $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    public function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('https://twitter.com/i/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return 'https://api.twitter.com/2/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get('https://api.twitter.com/2/users/me', [
            'headers' => ['Authorization' => 'Bearer '.$token],
            'query' => ['user.fields' => 'profile_image_url'],
        ]);

        return Arr::get(json_decode($response->getBody(), true), 'data');
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => $user['username'],
            'name' => $user['name'],
            'avatar' => $user['profile_image_url'],
        ]);
    }
}
