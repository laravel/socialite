<?php

namespace Laravel\Socialite\Jwt;

use Laravel\Socialite\AbstractUser;

class User extends AbstractUser
{
    /**
     * The email verification status.
     *
     * @var bool
     */
    public $email_verified;

    /**
     * The organization the user belongs to.
     *
     * @var string
     */
    public $organization;

    /**
     * Checks if the user's email is verified.
     *
     * @return bool
     */
    public function isEmailVerified()
    {
        return $this->email_verified;
    }

    /**
     * Sets the organization for the current user.
     *
     * @param  string $organization
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }
}
