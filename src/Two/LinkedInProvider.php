<?php

namespace Laravel\Socialite\Two;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;

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
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://www.linkedin.com/oauth/v2/authorization', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $basicProfile = $this->getBasicProfile($token);
        // $emailAddress = $this->getEmailAddress($token);

        return $basicProfile;
        return array_merge($basicProfile, $emailAddress);
    }

    /**
     * Get the basic profile fields for the user.
     *
     * @param  string  $token
     * @return array
     */
    protected function getBasicProfile($token)
    {
        $response = $this->getHttpClient()->get('https://api.linkedin.com/v2/userinfo', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ]
        ]);
        return (array) json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {

        $preferredLocale = Arr::get($user, 'locale.language', []);
        $firstName = Arr::get($user, 'given_name', '');
        $lastName = Arr::get($user, 'family_name', '');
        $avatar = Arr::get($user, 'picture', []);
        $originalAvatar = $avatar;

        return (new User)->setRaw($user)->map([
            'id' => $user['sub'],
            'nickname' => null,
            'name' => $firstName . ' ' . $lastName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => Arr::get($user, 'email'),
            'avatar' => $avatar,
            'avatar_original' => $originalAvatar,
        ]);
    }
}
