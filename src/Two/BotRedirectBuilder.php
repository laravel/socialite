<?php

namespace Laravel\Socialite;

use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Exceptions\GuildRequiredException;

class BotRedirectBuilder
{
    /**
     * The client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The permissions to request.
     *
     * @var int
     */
    protected $permissions = 0;

    /**
     * The guild to pre-fill the dropdown picker with.
     *
     * @var string
     */
    protected $guild;

    /**
     * Whether to disallow the user from changing the guild dropdown.
     *
     * @var bool
     */
    protected $disableGuildSelect = false;

    /**
     * Create a new bot redirect builder instance.
     *
     * @param  string  $clientId
     * @return void
     */
    public function __construct(string $clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Set the permissions to request.
     *
     * @param  int  $permissions
     * @return $this
     */
    public function permissions(int $permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Pre-fill the dropdown picker with a guild for the user.
     *
     * @param  string|int  $guild
     * @return $this
     */
    public function guild($guild)
    {
        $this->guild = (string) $guild;

        return $this;
    }

    /**
     * Disallow the user from changing the guild dropdown.
     *
     * @return $this
     */
    public function disableGuildSelect()
    {
        $this->disableGuildSelect = true;

        return $this;
    }

    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        return new RedirectResponse($this->getAuthUrl());
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string  $url
     * @return string
     */
    protected function buildAuthUrlFromBase($url)
    {
        return $url.'?'.http_build_query($this->getCodeFields(), '', '&', PHP_QUERY_RFC1738);
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @return array
     *
     * @throws \Laravel\Socialite\Exceptions\GuildRequiredException
     */
    protected function getCodeFields()
    {
        if ($this->disableGuildSelect && ! $this->guild) {
            throw new GuildRequiredException();
        }

        return [
            'client_id' => $this->clientId,
            'scope' => 'bot',
            'permissions' => $this->permissions,
            'guild_id' => $this->guild,
            'disable_guild_select' => $this->disableGuildSelect ? 'true' : 'false',
        ];
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @return string
     */
    protected function getAuthUrl()
    {
        return $this->buildAuthUrlFromBase('https://discord.com/api/oauth2/authorize');
    }
}