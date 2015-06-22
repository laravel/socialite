<?php namespace Laravel\Socialite\Two;

class LinkedInProvider extends AbstractProvider implements ProviderInterface
{

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['r_basicprofile', 'r_emailaddress'];
    
    /**
     * The fields that are included in the profile.
     * 
     * @var array
     */
    protected $fields = [
        'id', 'first-name', 'last-name', 'formatted-name',
        'email-address', 'headline', 'location', 'industry',
        'public-profile-url', 'picture-url', 'picture-urls::(original)',
    ];

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
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $fields = implode(',', $this->fields);
        $url = 'https://api.linkedin.com/v1/people/~:('.$fields.')';

        $response = $this->getHttpClient()->get($url, [
          'headers' => [
            'x-li-format' => 'json',
            'Authorization' => 'Bearer ' . $token,
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
            'id' => $user['id'], 'nickname' => null, 'name' => array_get($user, 'formattedName'),
            'email' => array_get($user, 'emailAddress'), 'avatar' => $user['pictureUrl'],
        ]);
    }
}
