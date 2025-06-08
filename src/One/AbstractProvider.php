<?php

namespace Laravel\Socialite\One;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\Provider as ProviderContract;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;

abstract class AbstractProvider implements ProviderContract
{
    /**
     * The HTTP request instance.
     */
    protected Request $request;

    /**
     * The OAuth server implementation.
     */
    protected Server $server;

    /**
     * A hash representing the last requested user.
     */
    protected ?string $userHash = null;

    /**
     * Create a new provider instance.
     */
    public function __construct(Request $request, Server $server)
    {
        $this->server = $server;
        $this->request = $request;
    }

    /**
     * Redirect the user to the authentication page for the provider.
     */
    public function redirect(): RedirectResponse
    {
        $tempCredentials = $this->server->getTemporaryCredentials();
        
        $this->request->session()->put('oauth.temp', $tempCredentials);

        return new RedirectResponse(
            $this->server->getAuthorizationUrl($tempCredentials)
        );
    }

    /**
     * Get the User instance for the authenticated user.
     *
     * @throws MissingVerifierException
     */
    public function user(): User
    {
        if (!$this->hasNecessaryVerifier()) {
            throw new MissingVerifierException('Invalid request. Missing OAuth verifier.');
        }

        $token = $this->getToken();
        $shouldBypassCache = $this->shouldBypassCache(
            $token->getIdentifier(), 
            $token->getSecret()
        );

        $user = $this->server->getUserDetails($token, $shouldBypassCache);

        return $this->mapUserToObject($user)
            ->setToken($token->getIdentifier(), $token->getSecret())
            ->setRaw($user->extra);
    }

    /**
     * Get a Social User instance from a known access token and secret.
     */
    public function userFromTokenAndSecret(string $token, string $secret): User
    {
        $tokenCredentials = new TokenCredentials();
        $tokenCredentials->setIdentifier($token);
        $tokenCredentials->setSecret($secret);

        $shouldBypassCache = $this->shouldBypassCache($token, $secret);
        $user = $this->server->getUserDetails($tokenCredentials, $shouldBypassCache);

        return $this->mapUserToObject($user)
            ->setToken($tokenCredentials->getIdentifier(), $tokenCredentials->getSecret())
            ->setRaw($user->extra);
    }

    /**
     * Map the user details to a User object.
     */
    protected function mapUserToObject(object $user): User
    {
        return (new User())->map([
            'id' => $user->uid,
            'nickname' => $user->nickname,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->imageUrl,
        ]);
    }

    /**
     * Get the token credentials for the request.
     *
     * @throws MissingTemporaryCredentialsException
     */
    protected function getToken(): TokenCredentials
    {
        $temp = $this->request->session()->get('oauth.temp');

        if (!$temp) {
            throw new MissingTemporaryCredentialsException(
                'Missing temporary OAuth credentials.'
            );
        }

        return $this->server->getTokenCredentials(
            $temp,
            $this->request->get('oauth_token'),
            $this->request->get('oauth_verifier')
        );
    }

    /**
     * Determine if the request has the necessary OAuth verifier.
     */
    protected function hasNecessaryVerifier(): bool
    {
        return $this->request->has(['oauth_token', 'oauth_verifier']);
    }

    /**
     * Determine if the user information cache should be bypassed.
     */
    protected function shouldBypassCache(string $token, string $secret): bool
    {
        $newHash = hash('sha256', $token.'_'.$secret);

        if ($this->userHash && $newHash !== $this->userHash) {
            $this->userHash = $newHash;
            return true;
        }

        $this->userHash ??= $newHash;
        return false;
    }

    /**
     * Set the request instance.
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }
}
