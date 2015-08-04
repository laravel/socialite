<?php

namespace Laravel\Socialite\One;

use Illuminate\Http\Request;
use InvalidArgumentException;
use League\OAuth1\Client\Server\Server;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Laravel\Socialite\Contracts\Provider as ProviderContract;

abstract class AbstractProvider implements ProviderContract
{
    /**
     * The HTTP request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * The OAuth server implementation.
     *
     * @var Server
     */
    protected $server;

    /**
     * Create a new provider instance.
     *
     * @param  Request  $request
     * @param  Server  $server
     * @return void
     */
    public function __construct(Request $request, Server $server)
    {
        $this->server = $server;
        $this->request = $request;
    }

    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return RedirectResponse
     */
    public function redirect()
    {
        $this->request->getSession()->set(
            'oauth.temp', $temp = $this->server->getTemporaryCredentials()
        );

        return new RedirectResponse($this->server->getAuthorizationUrl($temp));
    }

    /**
     * Get the User instance for the authenticated user.
     *
     * @return \Laravel\Socialite\One\User
     */
    public function user()
    {
        if (! $this->hasNecessaryVerifier()) {
            throw new InvalidArgumentException('Invalid request. Missing OAuth verifier.');
        }

        $user = $this->server->getUserDetails($token = $this->getToken());

        $instance = (new User)->setRaw($user->extra)
                ->setToken($token->getIdentifier(), $token->getSecret());

        return $instance->map([
            'id' => $user->uid, 'nickname' => $user->nickname,
            'name' => $user->name, 'email' => $user->email, 'avatar' => $user->imageUrl,
        ]);
    }

    /**
     * Get the token credentials for the request.
     *
     * @return \League\OAuth1\Client\Credentials\TokenCredentials
     */
    protected function getToken()
    {
        $temp = $this->request->getSession()->get('oauth.temp');

        return $this->server->getTokenCredentials(
            $temp, $this->request->get('oauth_token'), $this->request->get('oauth_verifier')
        );
    }

    /**
     * Determine if the request has the necessary OAuth verifier.
     *
     * @return bool
     */
    protected function hasNecessaryVerifier()
    {
        return $this->request->has('oauth_token') && $this->request->has('oauth_verifier');
    }

    /**
     * Set the request instance.
     *
     * @param  Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}
