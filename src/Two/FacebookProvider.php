<?php

namespace Laravel\Socialite\Two;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use phpseclib3\Crypt\RSA;
use phpseclib3\Math\BigInteger;

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
    protected $version = 'v23.0';

    /**
     * The user fields being requested.
     *
     * @var array
     */
    protected $fields = ['name', 'email', 'gender', 'verified', 'link', 'picture.width(1920)'];

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
     * Re-request a declined permission.
     *
     * @var bool
     */
    protected $reRequest = false;

    /**
     * The access token that was last used to retrieve a user.
     *
     * @var string|null
     */
    protected $lastToken;

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
        return $this->graphUrl.'/'.$this->version.'/oauth/access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::FORM_PARAMS => $this->getTokenFields($code),
        ]);

        $data = json_decode($response->getBody(), true);

        return Arr::add($data, 'expires_in', Arr::pull($data, 'expires'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $this->lastToken = $token;

        return $this->getUserByOIDCToken($token) ??
               $this->getUserFromAccessToken($token);
    }

    /**
     * Get user based on the OIDC token.
     *
     * @param  string  $token
     * @return array
     */
    protected function getUserByOIDCToken($token)
    {
        $kid = json_decode(base64_decode(explode('.', $token)[0]), true)['kid'] ?? null;

        if ($kid === null) {
            return null;
        }

        $data = (array) JWT::decode($token, $this->getPublicKeyOfOIDCToken($kid));

        throw_if($data['aud'] !== $this->clientId, new Exception('Token has incorrect audience.'));
        throw_if($data['iss'] !== 'https://www.facebook.com', new Exception('Token has incorrect issuer.'));

        $data['id'] = $data['sub'];

        if (isset($data['given_name'])) {
            $data['first_name'] = $data['given_name'];
        }

        if (isset($data['family_name'])) {
            $data['last_name'] = $data['family_name'];
        }

        return $data;
    }

    /**
     * Get the public key to verify the signature of OIDC token.
     *
     * @param  string  $id
     * @return \Firebase\JWT\Key
     */
    protected function getPublicKeyOfOIDCToken(string $kid)
    {
        $response = $this->getHttpClient()->get('https://limited.facebook.com/.well-known/oauth/openid/jwks/');

        $key = Arr::first(json_decode($response->getBody()->getContents(), true)['keys'], function ($key) use ($kid) {
            return $key['kid'] === $kid;
        });

        $key['n'] = new BigInteger(JWT::urlsafeB64Decode($key['n']), 256);
        $key['e'] = new BigInteger(JWT::urlsafeB64Decode($key['e']), 256);

        return new Key((string) RSA::load($key), 'RS256');
    }

    /**
     * Get user based on the access token.
     *
     * @param  string  $token
     * @return array
     */
    protected function getUserFromAccessToken($token)
    {
        $params = [
            'access_token' => $token,
            'fields' => implode(',', $this->fields),
        ];

        if (! empty($this->clientSecret)) {
            $params['appsecret_proof'] = hash_hmac('sha256', $token, $this->clientSecret);
        }

        $response = $this->getHttpClient()->get($this->graphUrl.'/'.$this->version.'/me', [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
            ],
            RequestOptions::QUERY => $params,
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => null,
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'avatar' => $avatarUrl = Arr::get($user, 'picture.data.url'),
            'avatar_original' => $avatarUrl,
            'profileUrl' => $user['link'] ?? null,
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

        if ($this->reRequest) {
            $fields['auth_type'] = 'rerequest';
        }

        return $fields;
    }

    /**
     * Set the user fields to request from Facebook.
     *
     * @param  array  $fields
     * @return $this
     */
    public function fields(array $fields)
    {
        $this->fields = $fields;

        return $this;
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

    /**
     * Re-request permissions which were previously declined.
     *
     * @return $this
     */
    public function reRequest()
    {
        $this->reRequest = true;

        return $this;
    }

    /**
     * Get the last access token used.
     *
     * @return string|null
     */
    public function lastToken()
    {
        return $this->lastToken;
    }

    /**
     * Specify which graph version should be used.
     *
     * @param  string  $version
     * @return $this
     */
    public function usingGraphVersion(string $version)
    {
        $this->version = $version;

        return $this;
    }
}
