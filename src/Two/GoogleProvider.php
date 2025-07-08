<?php

namespace Laravel\Socialite\Two;

use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;

class GoogleProvider extends AbstractProvider implements ProviderInterface
{
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
        return 'https://www.googleapis.com/oauth2/v4/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        if ($this->isJwtToken($token)) {
            return $this->getUserFromJwtToken($token);
        }

        $response = $this->getHttpClient()->get('https://www.googleapis.com/oauth2/v3/userinfo', [
            RequestOptions::QUERY => [
                'prettyPrint' => 'false',
            ],
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken($refreshToken)
    {
        $response = $this->getRefreshTokenResponse($refreshToken);

        return new Token(
            Arr::get($response, 'access_token'),
            Arr::get($response, 'refresh_token', $refreshToken),
            Arr::get($response, 'expires_in'),
            explode($this->scopeSeparator, Arr::get($response, 'scope', ''))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        // Deprecated: Fields added to keep backwards compatibility in 4.0. These will be removed in 5.0
        $user['id'] = Arr::get($user, 'sub');
        $user['verified_email'] = Arr::get($user, 'email_verified');
        $user['link'] = Arr::get($user, 'profile');

        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'sub'),
            'nickname' => Arr::get($user, 'nickname'),
            'name' => Arr::get($user, 'name'),
            'email' => Arr::get($user, 'email'),
            'avatar' => $avatarUrl = Arr::get($user, 'picture'),
            'avatar_original' => $avatarUrl,
        ]);
    }

    /**
     * Determine if the given token is a JWT (ID token).
     *
     * @param  string  $token
     * @return bool
     */
    protected function isJwtToken($token)
    {
        return substr_count($token, '.') === 2 && strlen($token) > 100;
    }

    /**
     * Get user data from Google ID token (JWT).
     *
     * @param  string  $idToken
     * @return array
     *
     * @throws \Exception
     */
    protected function getUserFromJwtToken($idToken)
    {
        try {
            $user = (array) JWT::decode(
                $idToken, JWK::parseKeySet($this->getGoogleJwks())
            );

            if (! isset($user['iss']) ||
                $user['iss'] !== 'https://accounts.google.com') {
                throw new Exception('Invalid ID token issuer.');
            }

            if (! isset($user['aud']) || $user['aud'] !== $this->clientId) {
                throw new Exception('Invalid ID token audience.');
            }

            return $user;
        } catch (Exception $e) {
            throw new Exception('Failed to verify Google JWT token: '.$e->getMessage());
        }
    }

    /**
     * Get Google's JSON Web Key Set for JWT verification.
     *
     * @return array
     */
    protected function getGoogleJwks()
    {
        $response = $this->getHttpClient()->get(
            'https://www.googleapis.com/oauth2/v3/certs'
        );

        return json_decode((string) $response->getBody(), true);
    }
}
