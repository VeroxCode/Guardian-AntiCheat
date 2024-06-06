<?php

namespace veroxcode\Guardian\Checks\World;

use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\item\Food;
use pocketmine\item\FoodSourceItem;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\MushroomStew;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\CheckManager;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\User\User;

class FastEat extends Check
{

    public const IGNORE = [ItemTypeIds::MUSHROOM_STEW, ItemTypeIds::BEETROOT_SOUP, ItemTypeIds::PUFFERFISH, ItemTypeIds::CHORUS_FRUIT];

    public function __construct()
    {
        parent::__construct("FastEat", CheckManager::WORLD);
    }

    public function onUseItem(InventoryTransactionPacket $packet, User $user): void
    {
        $trData = $packet->trData;
        $player = $user->getPlayer();
        $cache = $user->getCache();

        if ($trData instanceof UseItemTransactionData){
            $type = $trData->getActionType();

            if ($type == UseItemTransactionData::ACTION_CLICK_BLOCK || $type == UseItemTransactionData::ACTION_CLICK_AIR){
                if ($player->isUsingItem()){
                    return;
                }

                $cache["lastUse"] = (microtime(true) * 1000) - $player->getNetworkSession()->getPing();
                $user->setCache($cache);
            }
        }
    }

    public function onConsume(PlayerItemConsumeEvent $event, User $user): void
    {
        $player = $user->getPlayer();
        $consumedItem = $event->getItem();
        $cache = $user->getCache();

        if ($consumedItem instanceof Food && !in_array($consumedItem->getTypeId(), self::IGNORE)){
            $consumeTime = (microtime(true) * 1000) - $user->getCache()["lastUse"];
            if ($consumeTime < 1500){

                $cache["lastUse"] = (microtime(true) * 1000) - $player->getNetworkSession()->getPing();
                $user->setCache($cache);

                $user->increaseViolation($this->getName());
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                $event->cancel();
            }
        }

    }

}