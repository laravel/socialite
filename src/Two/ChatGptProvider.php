<?php

namespace Laravel\Socialite\Two;

use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;

class ChatGptProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['openid', 'profile', 'email'];

    /**
     * Indicates if PKCE should be used.
     *
     * @var bool
     */
    protected $usesPKCE = true;

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://auth.openai.com/api/accounts/authorize', $state);
    }

    /**
     * {@inheritdoc}
     *
     * Dynamic registration also expects "ext_agent_host_id" and "agent_name_hint" via with().
     */
    protected function getCodeFields($state = null)
    {
        return ['resource' => 'https://api.openai.com/v1'] + parent::getCodeFields($state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
            'code_verifier' => $this->request->session()->pull('code_verifier'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://auth.openai.com/api/accounts/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        if ($this->clientId === 'dynamic_agent_client' && $this->request->filled('client_id')) {
            $this->clientId = $this->request->input('client_id');
        }

        $response = $this->getAccessTokenResponse($this->getCode());

        // Identity-only clients may receive an ID token without an access token...
        $user = isset($response['access_token'])
            ? $this->getUserByToken($response['access_token'])
            : $this->getUserFromIdToken(Arr::get($response, 'id_token'));

        return $this->userInstance($response, ['client_id' => $this->clientId] + $user);
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://auth.openai.com/api/accounts/oauth/userinfo', [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer '.$token],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'sub'),
            'nickname' => Arr::get($user, 'nickname') ?? Arr::get($user, 'preferred_username'),
            'name' => Arr::get($user, 'name'),
            'email' => Arr::get($user, 'email'),
            'email_verified' => Arr::get($user, 'email_verified'),
            'avatar' => Arr::get($user, 'picture'),
        ]);
    }

    /**
     * Get the user data from a verified OpenID Connect ID token.
     *
     * @param  string|null  $idToken
     * @return array
     *
     * @throws \Exception
     */
    protected function getUserFromIdToken($idToken)
    {
        try {
            $user = (array) JWT::decode((string) $idToken, JWK::parseKeySet($this->getJwks()));

            if (($user['iss'] ?? null) !== 'https://auth.openai.com') {
                throw new Exception('Invalid ID token issuer.');
            }

            if (! in_array($this->clientId, (array) ($user['aud'] ?? []), true)) {
                throw new Exception('Invalid ID token audience.');
            }

            return $user;
        } catch (Exception $e) {
            throw new Exception('Failed to verify ChatGPT ID token: '.$e->getMessage());
        }
    }

    /**
     * Get OpenAI's JSON Web Key Set for ID token verification.
     *
     * @return array
     */
    protected function getJwks()
    {
        $response = $this->getHttpClient()->get('https://auth.openai.com/.well-known/jwks.json');

        return json_decode((string) $response->getBody(), true);
    }
}
