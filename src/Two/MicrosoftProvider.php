<?php

namespace Laravel\Socialite\Two;

use GuzzleHttp\ClientInterface;
use Illuminate\Http\Request;

class MicrosoftProvider extends AbstractProvider implements ProviderInterface
{
    protected $partnerDomain = 'windows.net';

    protected $tenantId = 'common';
    protected $stateless = true;
    protected $parameters = ['resource' => 'https://graph.windows.net'];
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
        'openid',
        'profile',
        'email',
    ];

    public function setPartnerDomain($parterDomain)
    {
        $this->partnerDomain = $parterDomain;
        $this->parameters['resource'] = "https://graph.{$parterDomain}";
    }

    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase("https://login.{$this->partnerDomain}/{$this->tenantId}/oauth2/authorize", $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return "https://login.chinacloudapi.cn/{$this->tenantId}/oauth2/token";
    }

    /**
     * Get the access token for the given code.
     *
     * @param  string  $code
     * @return string
     */
    public function getAccessToken($code)
    {
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            $postKey => $this->getTokenFields($code),
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
        $response = $this->getHttpClient()->get("https://graph.{$this->partnerDomain}/{$this->tenantId}/me/?", [
            'query' => [
                'api-version' => '1.6',
            ],
            'headers' => [
                'Accept' => 'application/json;odata=minimalmetadata',
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
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
            'id' => $user['immutableId'], 'nickname' => array_get($user, 'mailNickname'), 'name' => $user['displayName'],
            'email' => $user['mail'], 'avatar' => 'microsoft return no images',
        ]);
    }
}
