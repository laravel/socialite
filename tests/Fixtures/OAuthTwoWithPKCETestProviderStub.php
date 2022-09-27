<?php

namespace Laravel\Socialite\Tests\Fixtures;

class OAuthTwoWithPKCETestProviderStub extends OAuthTwoTestProviderStub
{
    protected bool $usesPKCE = true;
}
