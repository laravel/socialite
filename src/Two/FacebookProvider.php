<?php

namespace Laravel\Socialite\Two;

use Illuminate\Support\Arr;

class FacebookProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The base Facebook Graph URL.
     *
     * @var string
     */
    protected string $graphUrl = 'https://graph.facebook.com';

    /**
     * The Graph API version for the request.
     *
     * @var string
     */
    protected string $version = 'v3.3';

    /**
     * The user fields being requested.
     *
     * @var array
     */
    protected array $fields = ['name', 'email', 'gender', 'verified', 'link'];

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected array $scopes = ['email'];

    /**
     * Display the dialog in a popup view.
     *
     * @var bool
     */
    protected bool $popup = false;

    /**
     * Re-request a declined permission.
     *
     * @var bool
     */
    protected bool $reRequest = false;

    /**
     * The access token that was last used to retrieve a user.
     *
     * @var string|null
     */
    protected string|null $lastToken;

    /**
     * {@inheritdoc}
     */
    public function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('https://www.facebook.com/'.$this->version.'/dialog/oauth', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->graphUrl.'/'.$this->version.'/oauth/access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenResponse(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'form_params' => $this->getTokenFields($code),
        ]);

        $data = json_decode($response->getBody(), true);

        return Arr::add($data, 'expires_in', Arr::pull($data, 'expires'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token): array
    {
        $this->lastToken = $token;

        $params = [
            'access_token' => $token,
            'fields' => implode(',', $this->fields),
        ];

        if (! empty($this->clientSecret)) {
            $params['appsecret_proof'] = hash_hmac('sha256', $token, $this->clientSecret);
        }

        $response = $this->getHttpClient()->get($this->graphUrl.'/'.$this->version.'/me', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'query' => $params,
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User
    {
        $avatarUrl = $this->graphUrl.'/'.$this->version.'/'.$user['id'].'/picture';

        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => null,
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'avatar' => $avatarUrl.'?type=normal',
            'avatar_original' => $avatarUrl.'?width=1920',
            'profileUrl' => $user['link'] ?? null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCodeFields(string|null $state = null): array
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
    public function fields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Set the dialog to be displayed as a popup.
     *
     * @return $this
     */
    public function asPopup(): self
    {
        $this->popup = true;

        return $this;
    }

    /**
     * Re-request permissions which were previously declined.
     *
     * @return $this
     */
    public function reRequest(): self
    {
        $this->reRequest = true;

        return $this;
    }

    /**
     * Get the last access token used.
     *
     * @return string|null
     */
    public function lastToken(): string|null
    {
        return $this->lastToken;
    }

    /**
     * Specify which graph version should be used.
     *
     * @param  string  $version
     * @return $this
     */
    public function usingGraphVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }
}
