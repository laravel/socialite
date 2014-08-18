<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class FacebookProvider extends AbstractProvider implements ProviderInterface {

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
		return 'https://graph.facebook.com/oauth/access_token';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getUserByToken($token)
	{
		$response = $this->getHttpClient()->get('https://graph.facebook.com/me?access_token='.$token, [
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
			'email' => $user['email'], 'avatar' => 'https://graph.facebook.com/'.$user['id'].'/picture?type=normal',
		]);
	}

}