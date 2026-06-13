<?php

namespace Laravel\Socialite\Tests\Fixtures;

use Firebase\JWT\Key;
use Laravel\Socialite\Two\FacebookProvider;

class FacebookOIDCTestProviderStub extends FacebookProvider
{
    public const TEST_SECRET = 'abcdefghijklmnopqrstuvwxyz123456';

    /**
     * Get the public key to verify the signature of OIDC token.
     *
     * @param  string  $kid
     * @return \Firebase\JWT\Key
     */
    protected function getPublicKeyOfOIDCToken(string $kid)
    {
        return new Key(self::TEST_SECRET, 'HS256');
    }
}
