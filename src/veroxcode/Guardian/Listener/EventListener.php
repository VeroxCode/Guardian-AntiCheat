<?php

/*
 *
 *   _____                     _ _
 *  / ____|                   | (_)
 * | |  __ _   _  __ _ _ __ __| |_  __ _ _ __
 * | | |_ | | | |/ _` | '__/ _` | |/ _` | '_ \
 * | |__| | |_| | (_| | | | (_| | | (_| | | | |
 *  \_____|\__,_|\__,_|_|  \__,_|_|\__,_|_| |_|
 *                 _   _      _                _
 *     /\         | | (_)    | |              | |
 *    /  \   _ __ | |_ _  ___| |__   ___  __ _| |_
 *   / /\ \ | '_ \| __| |/ __| '_ \ / _ \/ _` | __|
 *  / ____ \| | | | |_| | (__| | | |  __/ (_| | |_
 * /_/    \_\_| |_|\__|_|\___|_| |_|\___|\__,_|\__|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Copyright (c) 2023 by VeroxCode <https://github.com/VeroxCode>
 */

declare(strict_types=1);

namespace veroxcode\Guardian\Listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\player\Player;
use veroxcode\Guardian\Buffers\AttackFrame;
use veroxcode\Guardian\Buffers\MovementFrame;
use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\User\User;
use veroxcode\Guardian\Utils\Random;

class EventListener implements Listener {

	public function onPacketReceive(DataPacketReceiveEvent $event) : void {
		$packet = $event->getPacket();
		$player = $event->getOrigin()->getPlayer();

		if ($player == null || Guardian::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString()) == null) {
			return;
		}

		$uuid = $player->getUniqueId()->toString();
		$user = Guardian::getInstance()->getUserManager()->getUser($uuid);

		if ($user == null) {
			return;
		}

		if ($packet instanceof InventoryTransactionPacket) {
			$data = $packet->trData;

			if ($data instanceof UseItemOnEntityTransactionData) {
				$NewBuffer = new AttackFrame(
					$this->getServerTick(),
					$player->getNetworkSession()->getPing(),
					$user->getLastAttack()
				);
				Guardian::getInstance()->getUserManager()->getUser($uuid)->addToAttackBuffer($NewBuffer);
			}
		}

		if ($packet instanceof PlayerAuthInputPacket) {
			$moveForward = Random::clamp(-0.98, 0.98, $packet->getMoveVecX());
			$moveStrafe = Random::clamp(-0.98, 0.98, $packet->getMoveVecZ());

			$user->setMoveForward($moveForward);
			$user->setMoveStrafe($moveStrafe);

			foreach (Guardian::getInstance()->getCheckManager()->getChecks() as $Check) {
				$Check->onMove($player, $packet, $user);
			}

			$NewBuffer = new MovementFrame(
				$this->getServerTick(),
				$packet->getTick(),
				$packet->getPosition(),
				new Vector2($packet->getPitch(), $packet->getYaw()),
				$packet->getHeadYaw(),
				$event->getOrigin()->getPlayer()->isOnGround(),
				$event->getOrigin()->getPlayer()->boundingBox
			);
			$user->addToMovementBuffer($NewBuffer);

			if ($user->getFirstClientTick() == 0 && $user->getFirstServerTick() == 0) {
				$user->setFirstServerTick($this->getServerTick());
				$user->setFirstClientTick($packet->getTick());
				$user->setTickDelay($this->getServerTick() - $packet->getTick());
			}

			if ($user->getInput() == 0) {
				$user->setInput($packet->getInputMode());
			}
		}
	}

	public function onAttack(EntityDamageByEntityEvent $event) : void {
		$damager = $event->getDamager();
		$victim = $event->getEntity();

		if ($victim instanceof Player) {
			$victimUser = Guardian::getInstance()->getUserManager()->getUser($victim->getUniqueId()->toString());
			$victimUser->setLastKnockbackTick($this->getServerTick());
		}

		if ($damager instanceof Player) {
			$user = Guardian::getInstance()->getUserManager()->getUser($damager->getUniqueId()->toString());
			foreach (Guardian::getInstance()->getCheckManager()->getChecks() as $Check) {
				$Check->onAttack($event, $user);
			}
			$user->setLastAttack($this->getServerTick());

			if ($user->isPunishNext()) {
				$user->setPunishNext(false);
				$event->cancel();
			}
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void {
		$player = $event->getPlayer();
		$user = Guardian::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString());

		foreach (Guardian::getInstance()->getCheckManager()->getChecks() as $Check) {
			$Check->onBlockBreak($event, $user);
		}
	}

	public function onMotion(EntityMotionEvent $event) {
		$entity = $event->getEntity();

		if ($entity instanceof Player) {
			$user = Guardian::getInstance()->getUserManager()->getUser($entity->getUniqueId()->toString());
			foreach (Guardian::getInstance()->getCheckManager()->getChecks() as $Check) {
				$Check->onMotion($event, $user);
			}
		}
	}


	public function onJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();
		$uuid = $player->getUniqueId()->toString();
		$user = new User($uuid);

		Guardian::getInstance()->getUserManager()->registerUser($user);

		foreach (Guardian::getInstance()->getCheckManager()->getChecks() as $Check) {
			$Check->onJoin($event, $user);
		}
	}


	public function onQuit(PlayerQuitEvent $event) : void {
		$player = $event->getPlayer();
		$uuid = $player->getUniqueId()->toString();

		Guardian::getInstance()->getUserManager()->unregisterUser($uuid);
	}

	public function getServerTick() : int {
		return Guardian::getInstance()->getServer()->getTick();
	}
}