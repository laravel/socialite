<?php

namespace Laravel\Socialite\Two;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;

class SlackProvider extends AbstractProvider
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['identity.basic', 'identity.email', 'identity.team', 'identity.avatar'];

    /**
     * The key used for scopes.
     *
     * @var string
     */
    protected $scopeKey = 'user_scope';

    /**
     * Indicate that the requested token should be for a bot user.
     *
     * @return $this
     */
    public function asBotUser()
    {
        $this->scopeKey = 'scope';

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://slack.com/oauth/v2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://slack.com/api/oauth.v2.access';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://slack.com/api/users.identity', [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer '.$token],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'user.id'),
            'name' => Arr::get($user, 'user.name'),
            'email' => Arr::get($user, 'user.email'),
            'avatar' => Arr::get($user, 'user.image_512'),
            'organization_id' => Arr::get($user, 'team.id'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCodeFields($state = null)
    {
        $fields = parent::getCodeFields($state);

        if ($this->scopeKey === 'user_scope') {
            $fields['scope'] = '';
            $fields['user_scope'] = $this->formatScopes($this->scopes, $this->scopeSeparator);
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::HEADERS => $this->getTokenHeaders($code),
            RequestOptions::FORM_PARAMS => $this->getTokenFields($code),
        ]);

        $result = json_decode($response->getBody(), true);

        if ($this->scopeKey === 'user_scope') {
            return $result['authed_user'];
        }

        return $result;
    }
}
