<?php

namespace Laravel\Socialite\One;

class TwitterProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if (! $this->hasNecessaryVerifier()) {
            throw new MissingVerifierException('Invalid request. Missing OAuth verifier.');
        }

        $user = $this->server->getUserDetails($token = $this->getToken(), $this->shouldBypassCache($token->getIdentifier(), $token->getSecret()));

        $extraDetails = [
            'location' => $user->location,
            'description' => $user->description,
        ];

        $instance = (new User)->setRaw(array_merge($user->extra, $user->urls, $extraDetails))
                ->setToken($token->getIdentifier(), $token->getSecret());

        return $instance->map([
            'id' => $user->uid,
            'nickname' => $user->nickname,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->imageUrl,
            'avatar_original' => str_replace('_normal', '', $user->imageUrl),
        ]);
    }

    /**
     * Set the access level the application should request to the user account.
     *
     * @param  string  $scope
     * @return void
     */
    public function scope(string $scope)
    {
        $this->server->setApplicationScope($scope);
    }
}
