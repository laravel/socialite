<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleProvider extends AbstractProvider implements ProviderInterface {

	/**
	 * The API key.
	 *
	 * @var string
	 */
	protected $apiKey;

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
	protected $scopes = [
        'https://www.googleapis.com/auth/plus.me',
		'https://www.googleapis.com/auth/plus.login',
		'https://www.googleapis.com/auth/plus.profile.emails.read',
	];
    
    /**
	 * Create a new google provider instance.
	 *
	 * @param  Request  $request
	 * @param  string  $clientId
	 * @param  string  $clientSecret
	 * @param  string  $redirectUrl
	 * @return void
	 */
	public function __construct($request, $apiKey, $clientId, $clientSecret, $redirectUrl)
	{
		parent::__construct($request, $clientId, $clientSecret, $redirectUrl);
        
        $this->apiKey = $apiKey;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getAuthUrl($state)
	{
		return $this->buildAuthUrlFromBase('https://accounts.google.com/o/oauth2/auth', $state);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getTokenUrl()
	{
		return 'https://accounts.google.com/o/oauth2/token';
	}

	/**
	 * Get the access token for the given code.
	 *
	 * @param  string  $code
	 * @return string
	 */
	public function getAccessToken($code)
	{
		$response = $this->getHttpClient()->post($this->getTokenUrl(), [
			'body' => $this->getTokenFields($code),
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
		$response = $this->getHttpClient()->get('https://www.googleapis.com/plus/v1/people/me?',
            [
                'query' => [
                    'fields' => 'name(familyName,givenName),nickname,emails/value,image,id',
                    'prettyPrint' => 'false',
                    'key' => $this->apiKey,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]
        );

		return json_decode($response->getBody(), true);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function mapUserToObject(array $user)
	{
        return (new User)->setRaw($user)->map([
			'id' => $user['id'], 'nickname' => array_get($user, 'nickname'), 'name' => array_get($user, 'displayName'),
			'email' => $user['emails'][0]['value'], 'avatar' => array_get($user, 'image'),
		]);
	}

    /**
	 * {@inheritdoc}
	 */
	public function user($token = false)
	{
		if ($this->hasInvalidState())
		{
			throw new InvalidStateException;
		}
        
        if(!$token)
            $token = $this->getAccessToken($this->getCode());

		$user = $this->mapUserToObject($this->getUserByToken(
			$token
		));

		return $user->setToken($token);
	}

}
