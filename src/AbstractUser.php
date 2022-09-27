<?php

namespace Laravel\Socialite;

use ArrayAccess;
use Laravel\Socialite\Contracts\User;

abstract class AbstractUser implements ArrayAccess, User
{
    /**
     * The unique identifier for the user.
     *
     * @var mixed
     */
    public mixed $id;

    /**
     * The user's nickname / username.
     *
     * @var string
     */
    public string $nickname;

    /**
     * The user's full name.
     *
     * @var string
     */
    public string $name;

    /**
     * The user's e-mail address.
     *
     * @var string
     */
    public string $email;

    /**
     * The user's avatar image URL.
     *
     * @var string
     */
    public string $avatar;

    /**
     * The user's raw attributes.
     *
     * @var array
     */
    public array $user;

    /**
     * Get the unique identifier for the user.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the nickname / username for the user.
     *
     * @return string
     */
    public function getNickname(): string
    {
        return $this->nickname;
    }

    /**
     * Get the full name of the user.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the e-mail address of the user.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Get the avatar / image URL for the user.
     *
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * Get the raw user array.
     *
     * @return array
     */
    public function getRaw(): array
    {
        return $this->user;
    }

    /**
     * Set the raw user array from the provider.
     *
     * @param  array  $user
     * @return $this
     */
    public function setRaw(array $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Map the given array onto the user's properties.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function map(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * Determine if the given raw user attribute exists.
     *
     * @param  string  $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->user);
    }

    /**
     * Get the given key from the raw user.
     *
     * @param  string  $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->user[$offset];
    }

    /**
     * Set the given attribute on the raw user array.
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->user[$offset] = $value;
    }

    /**
     * Unset the given value from the raw user array.
     *
     * @param  string  $offset
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset(mixed $offset): void
    {
        unset($this->user[$offset]);
    }
}
