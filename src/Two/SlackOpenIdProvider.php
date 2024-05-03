<?php

namespace Laravel\Socialite\Two;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;

class SlackOpenIdProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopes = ['openid', 'email', 'profile'];

    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://slack.com/openid/connect/authorize', $state);
    }

    protected function getTokenUrl()
    {
        return 'https://slack.com/api/openid.connect.token';
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://slack.com/api/openid.connect.userInfo', [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer '.$token],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'sub'),
            'nickname' => null,
            'name' => Arr::get($user, 'name'),
            'email' => Arr::get($user, 'email'),
            'avatar' => Arr::get($user, 'picture'),
            'organization_id' => Arr::get($user, 'https://slack.com/team_id'),
        ]);
    }
}
