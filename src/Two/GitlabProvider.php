<?php

namespace Laravel\Socialite\Two;

class GitlabProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected array $scopes = ['read_user'];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected string $scopeSeparator = ' ';

    /**
     * The Gitlab instance host.
     *
     * @var string
     */
    protected string $host = 'https://gitlab.com';

    /**
     * Set the Gitlab instance host.
     *
     * @param  string|null  $host
     * @return $this
     */
    public function setHost(string|null $host): self
    {
        if (! empty($host)) {
            $this->host = rtrim($host, '/');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase($this->host.'/oauth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->host.'/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get($this->host.'/api/v3/user', [
            'query' => ['access_token' => $token],
        ]);

        return json_decode($response->getBody(), true);
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
            'email' => $user['email'],
            'avatar' => $user['avatar_url'],
        ]);
    }
}
