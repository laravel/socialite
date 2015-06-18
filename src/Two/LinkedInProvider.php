<?php namespace Laravel\Socialite\Two;

use GuzzleHttp\ClientInterface;

class LinkedInProvider extends AbstractProvider implements ProviderInterface
{

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['r_basicprofile', 'r_emailaddress'];

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
        $fields = 'id,email-address,first-name,last-name,formatted-name,headline,picture-url,public-profile-url,location';

        $response = $this->getHttpClient()->get("https://api.linkedin.com/v1/people/~:({$fields})", [
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
