<?php

namespace Laravel\Socialite\Jwt;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\Provider;

class GoogleProvider implements Provider
{
    protected $user;
    protected $request;
    protected $clientId;
    protected $payload;

    public function __construct(Request $request, $clientId)
    {
        $this->request = $request;
        $this->clientId = $clientId;
    }

    /**
     * @throws Exception
     */
    public function redirect()
    {
       throw new Exception('Redirect is deprecated for the new Google Auth, see https://developers.google.com/identity/gsi/web/guides/migration#html_and_javascript');
    }


    /**
     * Get the User instance for the authenticated user.
     *
     * @throws InvalidJwtException
     * @return \Laravel\Socialite\Contracts\User
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }
        $jwt = $this->request->credential;
        if($jwt === null){
            throw new InvalidJwtException("Empty JWT payload");
        }
        if(!$this->verifyJwtSignature($jwt)){
            throw new InvalidJwtException('Invalid JWT signature');
        }
        if (!in_array(Arr::get($this->payload, 'iss'), ['accounts.google.com', 'https://accounts.google.com'])) {
            throw new InvalidJwtException('Invalid JWT issuer');
        }
        if (Arr::get($this->payload, 'aud') !== $this->clientId) {
            throw new InvalidJwtException('Client ID mismatch');
        }
        if (Arr::get($this->payload, 'exp') < time()) {
            throw new InvalidJwtException('Expired token');
        }
        if (Arr::get($this->payload, 'iat') > time()) {
            throw new InvalidJwtException('Token used before issued');
        }

        return $this->user = $this->mapUserToObject($this->payload)
            ->setOrganization(Arr::get($this->payload, 'hd'));
    }


    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array  $user
     * @return \Laravel\Socialite\Jwt\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'sub'),
            'nickname' => Arr::get($user, 'nickname'),
            'name' => Arr::get($user, 'name'),
            'email_verified' => Arr::get($user, 'email_verified'),
            'email' => Arr::get($user, 'email'),
            'avatar' => $avatarUrl = Arr::get($user, 'picture'),
            'avatar_original' => $avatarUrl,
        ]);
    }

    /**
     * Verifies the signature of the JWT issued, via Google Certs
     *
     * @param  string  $jwt
     * @return boolean
     */
    protected function verifyJwtSignature($jwt){
        $jwt_parts = explode('.', $jwt);

        $header = json_decode($this->base64UrlDecode($jwt_parts[0]), true);
        $this->payload = json_decode($this->base64UrlDecode($jwt_parts[1]), true);
        $signature = $this->base64UrlDecode($jwt_parts[2]);

        $key_id = Arr::get($header, 'kid');
        $certificates = json_decode(file_get_contents('https://www.googleapis.com/oauth2/v1/certs'), true);
        $publicKey = openssl_pkey_get_public($certificates[$key_id]);

        return openssl_verify($jwt_parts[0] . '.' . $jwt_parts[1], $signature, $publicKey , OPENSSL_ALGO_SHA256);
    }

    /**
     * Decodes a Base64 URL-encoded string.
     *
     * This function converts a Base64 URL-encoded string to its original form by
     * replacing the URL-safe characters '-' and '_' with their respective Base64
     * characters '+' and '/', and then decoding the string using the standard
     * Base64 algorithm.
     *
     * @param string $data The Base64 URL-encoded string to decode.
     * @return string The decoded string.
     */
    protected function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
