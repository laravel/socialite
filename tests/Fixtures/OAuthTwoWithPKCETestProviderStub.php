<?php

namespace Laravel\Socialite\Tests\Fixtures;

class OAuthTwoWithPKCETestProviderStub extends OAuthTwoTestProviderStub
{
    protected $usesPKCE = true;
}
