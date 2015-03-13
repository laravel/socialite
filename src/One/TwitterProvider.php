<?php namespace Laravel\Socialite\One;

class TwitterProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if (! $this->hasNecessaryVerifier()) {
            throw new \InvalidArgumentException("Invalid request. Missing OAuth verifier.");
        }

        $user = $this->server->getUserDetails($token = $this->getToken());

        $instance = (new User)->setRaw(array_merge($user->extra, $user->urls))
                ->setToken($token->getIdentifier(), $token->getSecret());

        return $instance->map([
            'id' => $user->uid, 'nickname' => $user->nickname,
            'name' => $user->name, 'email' => $user->email, 'avatar' => $user->imageUrl,
        ]);
    }

}

