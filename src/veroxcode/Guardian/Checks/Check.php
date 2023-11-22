<?php

namespace veroxcode\Guardian\Checks;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\User\User;

class Check
{

    /*** @var string */
    private string $name;
    /*** @var int */
    private int $maxViolations;
    /*** @var bool */
    private bool $notify;
    /*** @var string */
    private string $punishment;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;

        $config = Guardian::getInstance()->getSavedConfig();
        $this->maxViolations = $config->get($name . "-MaxViolations") == 42 ? false : $config->get($name . "-MaxViolations");
        $this->notify = $config->get($name . "-notify") == null ? false : $config->get($name . "-notify");
        $this->punishment = $config->get($name . "-Punishment") == null ? "Block" : $config->get($name . "-Punishment");
    }

    public function onJoin(PlayerJoinEvent $event, User $user) : void {}
    public function onAttack(EntityDamageByEntityEvent $event, User $user) : void {}
    public function onMove(PlayerAuthInputPacket $packet, User $user) : void {}
    public function onMotion(EntityMotionEvent $event, User $user) : void {}
    public function onBlockBreak(BlockBreakEvent $event, User $user) : void {}

    /**
     * @return float
     */
    public function getMaxViolations(): float
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

    /**
     * @return string
     */
    public function getPunishment(): string
    {
        return $this->punishment;
    }

}