<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class LinkedinProvider extends AbstractProvider implements ProviderInterface {

	/**
	 * The scopes being requested.
	 *
	 * @var array
	 */
	protected $scopes = [ 'r_basicprofile', 'r_emailaddress' ];

	/**
	 * The separating character for the requested scopes.
	 *
	 * @var string
	 */
	protected $scope_separator = ' ';

	/**
	 * The type of the encoding in the query.
	 *
	 * @var int Can be either PHP_QUERY_RFC3986 or PHP_QUERY_RFC1738.
	 */
	protected $encoding_type = PHP_QUERY_RFC3986;


	/**
	 * {@inheritdoc}
	 */
	protected function getAuthUrl($state)
	{
		return $this->buildAuthUrlFromBase('https://www.linkedin.com/uas/oauth2/authorization', $state);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getTokenUrl()
	{
		return 'https://www.linkedin.com/uas/oauth2/accessToken?grant_type=authorization_code';
	}

	/**
	 * Get the access token for the given code.
	 *
	 * LinkedIn access token MUST be requested with GET.
	 *
	 * @see https://developer.linkedin.com/forum/unable-verify-access-token
	 *
	 * @param  string  $code
	 * @return string
	 */
	public function getAccessToken($code)
	{
		$response = $this->getHttpClient()->post($this->getTokenUrl().(strpos($this->getTokenUrl(),'?')===false?'?':'&').
			http_build_query($this->getTokenFields($code), '', '&', $this->encoding_type));

		return $this->parseAccessToken($response->getBody());
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getUserByToken($token) {

		$response = $this->getHttpClient()->get('https://api.linkedin.com/v1/people/~:(id,formatted-name,picture-url,email-address)',[
			'headers' => [
				'Accept-Language' => 'en-US',
				'x-li-format'     => 'json',
				'Authorization'   => 'Bearer '.$token,
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
			'id' => $user['id'], 'nickname' => null, 'name' => $user['formattedName'],
			'email' => $user['emailAddress'], 'avatar' => ( isset($user['pictureUrl']) ? $user['pictureUrl'] : '' ),
		]);
	}
}
