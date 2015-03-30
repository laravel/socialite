<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class GithubProvider extends AbstractProvider implements ProviderInterface
{

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
        $this->token = $token;
        $response = $this->getHttpClient()->get('https://api.github.com/user?access_token='.$token, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
    
    /**
     * {@inheritdoc}
     */
    private function getUserEmailByToken($token)
    {
        $email = null;
		$response = $this->getHttpClient()->get('https://api.github.com/user/emails?access_token='.$token, [
				'headers' => [
						'Accept' => 'application/vnd.github.v3+json',
				],
		]);
		$userEmails = json_decode($response->getBody(), true);
        foreach($userEmails AS $key => $array) {
			if($array['primary'] == true && $array['verified'] == true) {
				$email = $array['email'];
			}
		}
		return $email;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        if(is_null(array_get($user, 'email'))) {
            $user['email'] = $this->getUserEmailByToken($this->token);
        }
        return (new User)->setRaw($user)->map([
            'id' => $user['id'], 'nickname' => $user['login'], 'name' => array_get($user, 'name'),
            'email' => array_get($user, 'email'), 'avatar' => $user['avatar_url'],
        ]);
    }
}
