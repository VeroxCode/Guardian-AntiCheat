<?php

namespace veroxcode\Checks;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use veroxcode\User\User;

class Check
{

    /*** @var string */
    private string $name;
    /*** @var int */
    private int $maxViolations;

    /**
     * @param string $name
     * @param int $maxViolations
     */
    public function __construct(string $name, int $maxViolations)
    {
        $this->name = $name;
        $this->maxViolations = $maxViolations;
    }

    public function onJoin(PlayerJoinEvent $event, User $user) : void {}
    public function onAttack(EntityDamageByEntityEvent $event, User $user) : void {}
    public function onMove(PlayerAuthInputPacket $packet, User $user) : void {}

    /**
     * @return int
     */
    public function getMaxViolations(): int
    {
        return $this->maxViolations;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

}