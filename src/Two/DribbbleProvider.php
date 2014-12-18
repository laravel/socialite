<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class DribbbleProvider extends AbstractProvider implements ProviderInterface {

  /**
   * The scopes being requested.
   *
   * @var array
   */
  protected $scopes = ['public'];

  /**
   * {@inheritdoc}
   */
  protected function getAuthUrl($state)
  {
    return $this->buildAuthUrlFromBase('https://dribbble.com/oauth/authorize', $state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getTokenUrl()
  {
    return 'https://dribbble.com/oauth/token';
  }

  /**
   * {@inheritdoc}
   */
  protected function getUserByToken($token)
  {
    $response = $this->getHttpClient()->get('https://api.dribbble.com/v1/user', [
      'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $token
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
      'id' => $user['id'], 'nickname' => $user['username'], 'name' => $user['name'],
      'email' => null, 'avatar' => $user['avatar_url'],
    ]);
  }

}