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
     * @return string
     */
    public function getNickname(): string;

    /**
     * Get the full name of the user.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the e-mail address of the user.
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Get the avatar / image URL for the user.
     *
     * @return string
     */
    public function getAvatar(): string;
}
