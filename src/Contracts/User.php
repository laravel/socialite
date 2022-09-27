<?php

namespace Laravel\Socialite\Contracts;

interface User
{
    /**
     * Get the unique identifier for the user.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Get the nickname / username for the user.
     *
     * @return string|null
     */
    public function getNickname(): string|null;

    /**
     * Get the full name of the user.
     *
     * @return string|null
     */
    public function getName(): string|null;

    /**
     * Get the e-mail address of the user.
     *
     * @return string|null
     */
    public function getEmail(): string|null;

    /**
     * Get the avatar / image URL for the user.
     *
     * @return string|null
     */
    public function getAvatar(): string|null;
}
