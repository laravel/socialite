<?php

namespace Laravel\Socialite\Two;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class LinkedInProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['openid', 'profile', 'email'];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://www.linkedin.com/oauth/v2/authorization', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token): array
    {
        return $this->getBasicProfile($token);
    }

    /**
     * Get the basic profile fields for the user.
     *
     * @param  string  $token
     * @return array
     *
     * @throws GuzzleException
     */
    protected function getBasicProfile($token): array
    {
        $response = $this->getHttpClient()->get('https://api.linkedin.com/v2/userinfo', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            RequestOptions::QUERY => [
                'projection' => '(sub,email,given_name,family_name,picture)',
            ],
        ]);

        return (array) json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'],
            'nickname' => null,
            'name' => $user['given_name'].' '.$user['family_name'],
            'first_name' => $user['given_name'],
            'last_name' => $user['family_name'],
            'email' => $user['email'],
            'avatar' => $user['picture'],
            'avatar_original' => $user['picture'],
        ]);
    }
}
