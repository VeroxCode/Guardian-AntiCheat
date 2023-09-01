<?php

namespace veroxcode\User;

use veroxcode\Guardian;

class UserManager
{

    private array $Users = [];

     /**
    * @return array
    */
    public function getUsers(): array
    {
        return $this->Users;
    }

    /**
     * @param User $user
     * @return void
     */
    public function registerUser(User $user) : void
    {
        $uuid = $user->getUUID();

        if (isset($this->Users[$uuid])){
            return;
        }

        $this->Users[$uuid] = $user;
        Guardian::getInstance()->getLogger()->info("Registered User");
    }

    /**
     * @param string $uuid
     * @return void
     */
    public function unregisterUser(string $uuid) : void
    {
        if (isset($this->Users[$uuid])){
            Guardian::getInstance()->getLogger()->info("Removed User");
            unset($this->Users[$uuid]);
        }
    }

    /**
     * @param string $uuid
     * @return User|null
     */
    public function getUser(string $uuid) : ?User
    {
        if (isset($this->Users[$uuid])){
            return $this->Users[$uuid];
        }
        return null;
    }

}