<?php namespace Laravel\Socialite\Two;

use Symfony\Component\HttpFoundation\RedirectResponse;

class EveonlineProvider extends AbstractProvider implements ProviderInterface
{

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [];
    protected $imageUrl = 'https://image.eveonline.com/Character/';

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => [
                //'Accept' => 'application/json',
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic '.$this->getEncodedAuth(),
            ],
            'body' => "grant_type=authorization_code&code=$code"
        ]);
        
        return $this->parseAccessToken($response->getBody());
    }
    
    protected function getEncodedAuth()
    {
        return base64_encode($this->clientId.':'.$this->clientSecret);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://login.eveonline.com/oauth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://login.eveonline.com/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://login.eveonline.com/oauth/verify', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
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
            'id' => $user['CharacterID'], 
            'name' => $user['CharacterName'], 
            'avatar' => $this->imageUrl.$user['CharacterID'].'_64.jpg', 
        ]);
    }
}
