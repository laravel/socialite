<?php

namespace Laravel\Socialite\Two;

use Illuminate\Support\Arr;

class AppleProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    protected $scopes = ['name', 'email'];

    /**
     * {@inheritdoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://appleid.apple.com/auth/authorize', $state) . "&response_mode=form_post";
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://appleid.apple.com/auth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return parent::getTokenFields($code) + ['grant_type' => 'authorization_code'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByAccessTokenResponse($response)
    {
         return json_decode(base64_decode(explode('.', Arr::get($response, 'id_token'))[1]), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        // Refresh_token
        // User info already in the token response, todo find method in the documentation, is this is possible?
        // No user meta data in the grant_type refresh_token, https://developer.apple.com/documentation/signinwithapplerestapi/generate_and_validate_tokens
        throw new \BadMethodCallException( "User info already in the authorization_code token response.");
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'],
            'nickname' => null,
            'name' => null,
            'email' => Arr::get($user, 'email_dummy'), //TODO After the apple update, we need to change this
            'avatar' => null,
        ]);
    }
}
