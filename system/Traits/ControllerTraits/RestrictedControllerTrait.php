<?php


namespace Traits\ControllerTraits;


use Entities\Group;
use Entities\User;

/**
 * Trait RestrictedBaseTrait
 * @package Traits\ControllerTraits
 */
trait RestrictedControllerTrait
{
    /**
     * @var bool
     */
    private $registered = false;

    /**
     * @var User|null
     */
    private $user;

    /**
     * @var Group|null
     */
    private $group;

    /**
     * @return User|null
     */
    protected function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return Group|null
     */
    protected function getGroup(): ?Group
    {
        return $this->group;
    }

    /**
     * @return bool
     */
    protected function isRegistered(): bool
    {
        return $this->registered;
    }
}