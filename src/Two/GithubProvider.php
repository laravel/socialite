<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class GithubProvider extends AbstractProvider implements ProviderInterface {

	/**
	 * The scopes being requested.
	 *
	 * @var array
	 */
	protected $scopes = ['user:email'];

	/**
	 * {@inheritdoc}
	 */
	protected function getAuthUrl($state)
	{
		return $this->buildAuthUrlFromBase('https://github.com/login/oauth/authorize', $state);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getTokenUrl()
	{
		return 'https://github.com/login/oauth/access_token';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getUserByToken($token)
	{
		$options = [
			'headers' => [
				'Accept' => 'application/vnd.github.v3+json',
			],
		];

		$response = $this->getHttpClient()->get('https://api.github.com/user?access_token='.$token, $options);
		$user = json_decode($response->getBody(), true);

		if (in_array('user:email', $this->scopes))
		{
			$response = $this->getHttpClient()->get('https://api.github.com/user/emails?access_token='.$token, $options);
			$emails = json_decode($response->getBody(), true);
			foreach ($emails as $email)
			{
				if ($email['primary'] && $email['verified'])
				{
					$user['email'] = $email['email'];
					break;
				}
			}
		}

		return $user;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function mapUserToObject(array $user)
	{
		return (new User)->setRaw($user)->map([
			'id' => $user['id'], 'nickname' => $user['login'], 'name' => $user['name'],
			'email' => $user['email'], 'avatar' => $user['avatar_url'],
		]);
	}

}
