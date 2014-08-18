<?php namespace Laravel\Socialite\Two;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractProvider {

	/**
	 * The client ID.
	 *
	 * @var string
	 */
	protected $clientId;

	/**
	 * The client secret.
	 *
	 * @var string
	 */
	protected $clientSecret;

	/**
	 * The scopes being requested.
	 *
	 * @var array
	 */
	protected $scopes = [];

	/**
	 * The HTTP request instance.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Create a new provider instance.
	 *
	 * @param  Request  $request
	 * @param  string  $clientId
	 * @param  string  $clientSecret
	 * @param  string  $redirectUrl
	 * @return void
	 */
	public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl)
	{
		$this->request = $request;
		$this->clientId = $clientId;
		$this->redirectUrl = $redirectUrl;
		$this->clientSecret = $clientSecret;
	}

	/**
	 * Get the authentication URL for the provider.
	 *
	 * @param  string  $state
	 * @return string
	 */
	abstract protected function getAuthUrl($state);

	/**
	 * Get the token URL for the provider.
	 *
	 * @return string
	 */
	abstract protected function getTokenUrl();

	/**
	 * Get the raw user for the given access token.
	 *
	 * @param  string  $token
	 * @return array
	 */
	abstract protected function getUserByToken($token);

	/**
	 * Map the raw user array to a Socialite User instance.
	 *
	 * @param  array  $user
	 * @return \Laravel\Socialite\User
	 */
	abstract protected function mapUserToObject(array $user);

	/**
	 * Redirect the user of the application to the provider's authentication screen.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function redirect()
	{
		$this->request->getSession()->set(
			'state', $state = sha1(time().$this->request->getSession()->get('_token'))
		);

		return new RedirectResponse($this->getAuthUrl($state));
	}

	/**
	 * Get the authentication URL for the provider.
	 *
	 * @param  string  $url
	 * @param  string  $state
	 * @return string
	 */
	protected function buildAuthUrlFromBase($url, $state)
	{
		$session = $this->request->getSession();

		return $url.'?'.http_build_query([
			'client_id' => $this->clientId, 'redirect_url' => $this->redirectUrl,
			'scope' => $this->formatScopes($this->scopes), 'state' => $state,
		]);
	}

	/**
	 * Format the given scopes.
	 *
	 * @param  array  $scopes
	 * @return string
	 */
	protected function formatScopes(array $scopes)
	{
		return implode(',', $scopes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function user()
	{
		if ($this->hasInvalidState())
		{
			throw new InvalidStateException;
		}

		$user = $this->mapUserToObject($this->getUserByToken(
			$token = $this->getAccessToken($this->getCode())
		));

		return $user->setToken($token);
	}

	/**
	 * Determine if the current request / session has a mismatching "state".
	 *
	 * @return bool
	 */
	protected function hasInvalidState()
	{
		$session = $this->request->getSession();

		return ! ($this->request->input('state') === $session->get('state'));
	}

	/**
	 * Get the access token for the given code.
	 *
	 * @param  string  $code
	 * @return string
	 */
	public function getAccessToken($code)
	{
		$response = $this->getHttpClient()->post($this->getTokenUrl(), [
			'headers' => $this->getTokenHeaders(), 'body' => $this->getTokenFields($code),
		]);

		return $this->parseAccessToken($response->getBody());
	}

	/**
	 * Get the headers for the access token request.
	 *
	 * @return array
	 */
	protected function getTokenHeaders()
	{
		return ['Accept' => 'application/json'];
	}

	/**
	 * Get the POST fields for the token request.
	 *
	 * @param  string  $code
	 * @return array
	 */
	protected function getTokenFields($code)
	{
		return [
			'client_id' => $this->clientId, 'client_secret' => $this->clientSecret, 'code' => $code,
		];
	}

	/**
	 * Get the access token from the token response body.
	 *
	 * @param  string  $body
	 * @return string
	 */
	protected function parseAccessToken($body)
	{
		return json_decode($body, true)['access_token'];
	}

	/**
	 * Get the code from the request.
	 *
	 * @return string
	 */
	protected function getCode()
	{
		return $this->request->input('code');
	}

	/**
	 * Set the scopes of the requested access.
	 *
	 * @param  array  $scopes
	 * @return $this
	 */
	public function scopes(array $scopes)
	{
		$this->scopes = $scopes;

		return $this;
	}

	/**
	 * Get a fresh instance of the Guzzle HTTP client.
	 *
	 * @return \GuzzleHttp\Client
	 */
	protected function getHttpClient()
	{
		return new \GuzzleHttp\Client;
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