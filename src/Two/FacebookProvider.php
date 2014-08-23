<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class FacebookProvider extends AbstractProvider implements ProviderInterface {

	/**
	 * base Graph URL.
	 *
	 * @var string
	 */
	protected $graphURL = 'https://graph.facebook.com';
	
	/**
	 * The Graph API version for the request
	 *
	 * @var string
	 */
	protected $version = 'v2.1';
  
	/**
	 * The scopes being requested.
	 *
	 * @var array
	 */
	protected $scopes = ['email'];

	/**
	 * {@inheritdoc}
	 */
	protected function getAuthUrl($state)
	{
		return $this->buildAuthUrlFromBase('https://www.facebook.com/dialog/oauth', $state);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getTokenUrl()
	{
		return $this->graphURL . '/oauth/access_token';
	}

	/**
	 * Get the access token for the given code.
	 *
	 * @param  string  $code
	 * @return string
	 */
	public function getAccessToken($code)
	{
		$response = $this->getHttpClient()->get($this->getTokenUrl(), [
			'query' => $this->getTokenFields($code),
		]);

		return $this->parseAccessToken($response->getBody());
	}

	/**
	 * {@inheritdoc}
	 */
	protected function parseAccessToken($body)
	{
		parse_str($body);

		return $access_token;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getUserByToken($token)
	{
		$response = $this->getHttpClient()->get($this->graphURL.'/'. $this->version .'/me?access_token='.$token, [
			'headers' => [
				'Accept' => 'application/json',
			],
		]);

		return json_decode($response->getBody(), true);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function mapUserToObject(array $user)
	{
		return (new User)->setRaw($user)->map([
			'id' => $user['id'], 'nickname' => null, 'name' => $user['first_name'].' '.$user['last_name'],
			'email' => $user['email'], 'avatar' => $this->graphURL.'/'. $this->version .'/'.$user['id'].'/picture?type=normal',
		]);
	}

}
