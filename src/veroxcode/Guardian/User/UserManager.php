<?php

namespace veroxcode\Guardian\User;

use veroxcode\Guardian\Guardian;

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
    }

    /**
     * @param string $uuid
     * @return void
     */
    public function unregisterUser(string $uuid) : void
    {
        if (isset($this->Users[$uuid])){
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
            if ($this->Users[$uuid] !== null){
                return $this->Users[$uuid];
            }
        }

        $player = Guardian::getInstance()->getServer()->getPlayerByRawUUID($uuid);

        if ($player != null){
            $user = new User($player, $uuid);
            $this->registerUser($user);
            return $user;
        }
        return null;
    }

}
