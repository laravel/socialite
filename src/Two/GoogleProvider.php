<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleProvider extends AbstractProvider implements ProviderInterface {

	/**
	 * {@inheritdoc}
	 */
	protected function getAuthUrl($state)
	{
		return $this->buildAuthUrlFromBase('https://accounts.google.com/o/oauth2/auth', $state);
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
		return 'https://accounts.google.com/o/oauth2/token';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getUserByToken($token)
	{
		$response = $this->getHttpClient()->get('https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$token, [
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
			'id' => $user['id'], 'nickname' => null, 'name' => $user['given_name'].' '.$user['family_name'],
			'email' => $user['email'], 'avatar' => array_get($user, 'picture'),
		]);
	}

}