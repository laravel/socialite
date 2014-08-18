<?php namespace Laravel\Socialite\One;

use Illuminate\Http\Request;
use League\OAuth1\Client\Server\Server;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractProvider {

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
		if ( ! $this->hasNecessaryVerifier())
		{
			throw new \InvalidArgumentException("Invalid request. Missing OAuth verifier.");
		}

		$temp = $this->request->getSession()->get('oauth.temp');

		$token = $this->server->getTokenCredentials(
			$temp, $this->request->get('oauth_token'), $this->request->get('oauth_verifier')
		);

		$user = $this->server->getUserDetails($token);

		$instance = (new User)->setRaw($user->extra)
                ->setToken($token->getIdentifier(), $token->getSecret());

        return $instance->map([
			'id' => $user->uid, 'nickname' => $user->nickname,
			'name' => $user->firstName.' '.$user->lastName,
			'email' => $user->email, 'avatar' => $user->imageUrl,
        ]);
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