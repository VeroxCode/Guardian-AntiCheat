<?php

namespace veroxcode\Checks;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use veroxcode\Guardian;
use veroxcode\User\User;

class Check
{

    /*** @var string */
    private string $name;
    /*** @var int */
    private int $maxViolations;
    /*** @var bool */
    private bool $notify;

    /**
     * @param string $name
     * @param int $maxViolations
     */
    public function __construct(string $name, int $maxViolations)
    {
        $this->name = $name;
        $this->maxViolations = $maxViolations;

        $config = Guardian::getInstance()->getConfig();
        $this->notify = $config->get($name . "-notify");
    }

    public function onJoin(PlayerJoinEvent $event, User $user) : void {}
    public function onAttack(EntityDamageByEntityEvent $event, User $user) : void {}
    public function onMove(Player $player, PlayerAuthInputPacket $packet, User $user) : void {}

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

    /**
     * @param bool $notify
     */
    public function setNotify(bool $notify): void
    {
        $this->notify = $notify;
    }

    /**
     * @return bool
     */
    public function hasNotify(): bool
    {
        return $this->notify;
    }

}