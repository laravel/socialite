<?php

namespace Laravel\Socialite\Jwt;

use Laravel\Socialite\AbstractUser;

class User extends AbstractUser
{
   public $email_verified;

   public $organization;


   public function isEmailVerified()
   {
      return $this->email_verified;
   }

    /**
     * Set the organization for current user
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
