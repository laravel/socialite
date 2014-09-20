<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class RedditProvider extends AbstractProvider implements ProviderInterface {

	/**
	 * The scopes being requested.
	 *
	 * @var array
	 */
	protected $scopes = ['identity'];

	/**
	 * {@inheritdoc}
	 */
	protected function getAuthUrl($state, $duration = 'temporary')
	{
		return $this->buildAuthUrlFromBase('https://ssl.reddit.com/api/v1/authorize', $state, $duration);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getTokenUrl()
	{
		return 'https://ssl.reddit.com/api/v1/access_token';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getUserByToken($token)
	{
		$response = $this->getHttpClient()->get('https://oauth.reddit.com/api/v1/me', [
			'headers' => ['Authorization' => 'bearer '.$token]
		]);

		return json_decode($response->getBody(), true);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function mapUserToObject(array $user)
	{
		return (new User)->setRaw($user)->map([
			'id' => $user['id'], 'nickname' => $user['name'], 'name' => $user['name'],
			'email' => $user['has_verified_email'], 'avatar' => $user['id'],
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function redirect($duration = 'temporary')
	{
		$this->request->getSession()->set(
			'state', $state = sha1(time().$this->request->getSession()->get('_token'))
		);

		return new RedirectResponse($this->getAuthUrl($state, $duration));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildAuthUrlFromBase($url, $state, $duration)
	{
		$session = $this->request->getSession();

		return $url.'?'.http_build_query([
			'client_id' => $this->clientId, 'redirect_uri' => $this->redirectUrl,
			'scope' => $this->formatScopes($this->scopes), 'duration' => $duration, 'state' => $state,
			'response_type' => 'code',
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAccessToken($code)
	{
		$response = $this->getHttpClient()->post($this->getTokenUrl(), [
			'headers' => ['Accept' => 'application/json'],
			'auth' => [$this->clientId, $this->clientSecret],
			'body' => $this->getTokenFields($code),
		]);

		return $this->parseAccessToken($response->getBody());
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getTokenFields($code)
	{
		return [
			'grant_type' => 'authorization_code',
			'code' => $code, 'redirect_uri' => $this->redirectUrl
		];
	}

}