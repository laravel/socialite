<?php namespace Laravel\Socialite\Two;

use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FacebookProvider extends AbstractProvider implements ProviderInterface
{

    /**
     * The base Facebook Graph URL.
     *
     * @var string
     */
    protected $graphUrl = 'https://graph.facebook.com';

    /**
     * The Graph API version for the request.
     *
     * @var string
     */
    protected $version = 'v2.3';

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['email'];

    /**
     * Display the dialog in a popup view.
     *
     * @var bool
     */
    protected $popup = false;

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://www.facebook.com/'.$this->version.'/dialog/oauth', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return $this->graphUrl.'/oauth/access_token';
    }

    /**
     * Get the access token for the given code.
     *
     * @param  string  $code
     * @return string
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'exceptions' => false,
            'query' => $this->getTokenFields($code),
        ]);
        if ($response->getStatusCode() != 200) {
          $body = json_decode($response->getBody(), true);
          throw new Exception($body['error']['message']);
        }

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessToken($body)
    {
        parse_str($body);

        return $access_token;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->graphUrl.'/'. $this->version .'/me?access_token='.$token, [
            'exceptions' => false,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $body = json_decode($response->getBody(), true);
        if ($response->getStatusCode() != 200) {
          throw new Exception($body['error']['message']);
        }

        return $body;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $avatarUrl = $this->graphUrl.'/'.$this->version.'/'.$user['id'].'/picture';

        $firstName = isset($user['first_name']) ? $user['first_name'] : null;

        $lastName = isset($user['last_name']) ? $user['last_name'] : null;

        return (new User)->setRaw($user)->map([
            'id' => $user['id'], 'nickname' => null, 'name' => $firstName.' '.$lastName,
            'email' => isset($user['email']) ? $user['email'] : null, 'avatar' => $avatarUrl.'?type=normal',
            'avatar_original' => $avatarUrl.'?width=1920',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCodeFields($state = null)
    {
        $fields = parent::getCodeFields($state);

        if ($this->popup) {
            $fields['display'] = 'popup';
        }

        return $fields;
    }

    /**
     * Set the dialog to be displayed as a popup.
     *
     * @return $this
     */
    public function asPopup()
    {
        $this->popup = true;

        return $this;
    }
}
