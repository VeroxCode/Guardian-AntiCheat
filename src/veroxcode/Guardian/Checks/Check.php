<?php

namespace veroxcode\Guardian\Checks;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
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
    /*** @var int */
    private int $alertFrequency;
    /*** @var bool */
    private bool $notify;
    /*** @var bool */
    private bool $enabled;
    /*** @var string */
    private string $punishment;
    /*** @var string */
    private string $category;

    /**
     * @param string $name
     * @param string $category
     */
    public function __construct(string $name, string $category)
    {
        $this->name = $name;
        $this->category = $category;

        $config = Guardian::getInstance()->getSavedConfig();
        $this->enabled = $config->get("$name-enabled") ?? true;
        $this->maxViolations = $config->get("$name-MaxViolations") ?? 30;
        $this->notify = $config->get("$name-notify") ?? true;
        $this->punishment = $config->get("$name-Punishment") ?? "Cancel";
        $this->alertFrequency = $config->get("$name-AlertFrequency");
    }

    public function onJoin(PlayerLoginEvent $event, User $user) : void {}
    public function onAttack(EntityDamageByEntityEvent $event, User $user) : void {}
    public function onMove(PlayerAuthInputPacket $packet, User $user) : void {}
    public function onMotion(EntityMotionEvent $event, User $user) : void {}
    public function onBlockBreak(BlockBreakEvent $event, User $user) : void {}
    public function onUseItem(InventoryTransactionPacket $packet, User $user) : void {}
    public function onConsume(PlayerItemConsumeEvent $event, User $user) : void {}

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getMaxViolations(): float
    {
        return $this->maxViolations;
    }

    /**
     * @param int $maxViolations
     */
    public function setMaxViolations(int $maxViolations): void
    {
        $this->maxViolations = $maxViolations;
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

    /**
     * @param string $punishment
     */
    public function setPunishment(string $punishment): void
    {
        $this->punishment = $punishment;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return int
     */
    public function getAlertFrequency(): int
    {
        return $this->alertFrequency;
    }

    /**
     * @param int $alertFrequency
     */
    public function setAlertFrequency(int $alertFrequency): void
    {
        $this->alertFrequency = $alertFrequency;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

}