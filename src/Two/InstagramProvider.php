<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class InstagramProvider extends AbstractProvider implements ProviderInterface {

	/**
	 * {@inheritdoc}
	 */
	protected function getAuthUrl($state)
	{
		return $this->buildAuthUrlFromBase('https://api.instagram.com/oauth/authorize', $state);
	}

	/**
	 * Format the given scopes.
	 *
	 * @param  array  $scopes
	 * @return string
	 */
	protected function formatScopes(array $scopes)
	{
		return implode(' ', $scopes);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getTokenUrl()
	{
		return 'https://api.instagram.com/oauth/access_token';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getUserByToken($token)
	{
		$options = ['headers' => ['Accept' => 'application/json']];
		$endpoint = 'https://api.instagram.com/v1/users/self?access_token='.$token;
		$response = $this->getHttpClient()->get($endpoint, $options)->json();

		return $response['data'];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function mapUserToObject(array $user)
	{
		return (new User)->setRaw($user)->map([
			'id' => $user['id'], 'nickname' => $user['username'], 'name' => $user['full_name'],
			'email' => null, 'avatar' => $user['profile_picture'],
		]);
	}

}
