<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class DropboxProvider extends AbstractProvider implements ProviderInterface {

	/**
	 * {@inheritdoc}
	 */
	protected function getAuthUrl($state)
	{
		return $this->buildAuthUrlFromBase('https://www.dropbox.com/1/oauth2/authorize', $state);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getTokenUrl()
	{
		return 'https://api.dropbox.com/1/oauth2/token';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getUserByToken($token)
	{
		$response = $this->getHttpClient()->get('https://api.dropbox.com/1/account/info?access_token='.$token);

		return json_decode($response->getBody(), true);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function mapUserToObject(array $user)
	{
		return (new User)->setRaw($user)->map([
			'id'       => $user['uid'],
			'nickname' => $user['name_details']['familiar_name'],
			'name'     => $user['display_name'],
			'email'    => $user['email'],
			'avatar'   => '//www.gravatar.com/avatar/' . md5($user['email']),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAccessToken($code)
	{
		$response = $this->getHttpClient()->post($this->getTokenUrl(), [
			'body' => $this->getTokenFields($code),
		]);

		return $this->parseAccessToken($response->getBody());
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getTokenFields($code)
	{
		return array_merge(parent::getTokenFields($code), ['grant_type' => 'authorization_code']);
	}

}
