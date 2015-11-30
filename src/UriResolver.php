<?php

namespace Laravel\Socialite;

use InvalidArgumentException;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;

class UriResolver
{
    /**
     * @var \Illuminate\Contracts\Routing\UrlGeneratorContract;
     */
    protected $urlGenerator;

    /**
     * Create a new Uri Resolver instance.
     *
     * @param UrlGeneratorContract $urlGenerator
     * @return void
     */
    public function __construct(UrlGeneratorContract $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function resolve($redirectUri)
    {
        try {
            return $this->urlGenerator->route($redirectUri, [], true);
        } catch (InvalidArgumentException $e) {
            // No route with the provided name exists.
            // We will pass the redirect uri to UrlGenerator.
            return $this->urlGenerator->to($redirectUri);
        }
    }
}
