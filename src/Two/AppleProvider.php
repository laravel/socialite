<?php

namespace Laravel\Socialite\Two;

use Illuminate\Support\Arr;

class AppleProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    protected $scopes = ['name'];
    //TODO AFTER THE UPDATE BACK TO EMAIL protected $scopes = ['name', 'email'];

    /**
     * {@inheritdoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        //TODO After the apple update we could delete this
        if (in_array('email', $this->getScopes())) {
            throw new \InvalidArgumentException("Scope email not implemented yet, see https://forums.developer.apple.com/thread/118209");
        }

        return $this->buildAuthUrlFromBase('https://appleid.apple.com/auth/authorize', $state);
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
    public function user()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $response = $this->getAccessTokenResponse($this->getCode());

        // User info already in the token response, extra metadata request is not needed, skipping method getUserByToken
        // See: https://forums.developer.apple.com/thread/118209
        $id_token = json_decode(base64_decode(explode('.', Arr::get($response, 'id_token'))[1]), true);
        $token = Arr::get($response, 'access_token');

        $user = $this->mapUserToObject($id_token);

        return $user->setToken($token)
            ->setRefreshToken(Arr::get($response, 'refresh_token'))
            ->setExpiresIn(Arr::get($response, 'expires_in'));

    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        // User info already in the token response
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'],
            'nickname' => null,
            'name' => Arr::get($user, 'apple_update_name'), //TODO After the apple update, we need to change this
            'email' => Arr::get($user, 'apple_update_email'),
            'avatar' => null,
        ]);
    }
}
