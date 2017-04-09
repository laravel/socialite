<?php namespace Laravel\Socialite\Two;

use GuzzleHttp\ClientInterface;

class TwitchProvider extends AbstractProvider implements ProviderInterface
{

	/**
	 * The separating character for the requested scopes.
	 *
	 * @var string
	 */
	protected $scopeSeparator = ' ';

	/**
	 * The scopes being requested.
	 *
	 * @var array
	 */
	protected $scopes = ['user_read'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://api.twitch.tv/kraken/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://api.twitch.tv/kraken/oauth2/token';
    }

	/**
	 * Get the access token for the given code.
	 *
	 * @param  string  $code
	 * @return string
	 */
	public function getAccessToken($code)
	{
		$postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';

		$response = $this->getHttpClient()->post($this->getTokenUrl(), [
			$postKey => $this->getTokenFields($code),
		]);

		return $this->parseAccessToken($response->getBody());
	}

	/**
	 * Get the POST fields for the token request.
	 *
	 * @param  string  $code
	 * @return array
	 */
	protected function getTokenFields($code)
	{
		return array_add(
			parent::getTokenFields($code), 'grant_type', 'authorization_code'
		);
	}

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
		$response = $this->getHttpClient()->get('https://api.twitch.tv/kraken/user?', [
			'headers' => [
				'Accept' => 'application/vnd.twitchtv.v3+json',
				'Authorization' => 'OAuth ' . $token,
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
            'id' => $user['_id'], 'nickname' => $user['display_name'], 'name' => array_get($user, 'name'),
            'email' => array_get($user, 'email'), 'avatar' => $user['logo'],
        ]);
    }
}
