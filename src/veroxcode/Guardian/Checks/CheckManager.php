<?php

namespace veroxcode\Guardian\Checks;

use veroxcode\Guardian\Checks\Combat\AutoClicker;
use veroxcode\Guardian\Checks\Combat\Hitbox;
use veroxcode\Guardian\Checks\Combat\ImpossibleRotations;
use veroxcode\Guardian\Checks\Combat\Reach;
use veroxcode\Guardian\Checks\Combat\RotationsA;
use veroxcode\Guardian\Checks\Movement\Fly;
use veroxcode\Guardian\Checks\Movement\Speed;
use veroxcode\Guardian\Checks\Movement\Timer;
use veroxcode\Guardian\Checks\Packets\BadPacketsA;
use veroxcode\Guardian\Checks\Packets\BadPacketsB;
use veroxcode\Guardian\Checks\World\FastEat;
use veroxcode\Guardian\Checks\World\GhostHand;

class CheckManager
{

    /*** @var Check[] */
    public array $Checks = [];
    public array $Punishments = ["Cancel", "Kick", "Ban"];

    public const COMBAT = "Combat";
    public const MOVEMENT = "Movement";
    public const PLAYER = "Player";
    public const WORLD = "World";

    public function __construct()
    {
        $this->Checks[] = new Reach();
        $this->Checks[] = new Hitbox();
        //$this->Checks[] = new RotationsA();
        $this->Checks[] = new Timer();
        $this->Checks[] = new AutoClicker();
        $this->Checks[] = new BadPacketsA();
        $this->Checks[] = new BadPacketsB();
        $this->Checks[] = new GhostHand();
        $this->Checks[] = new ImpossibleRotations();
        $this->Checks[] = new FastEat();
        $this->Checks[] = new Speed();
        $this->Checks[] = new Fly();
    }

    /**
     * @return Check[]
     */
    public function getChecks() : array
    {
        return $this->Checks;
    }

    /**
    * @return array
    */
    public function getPunishments(): array
    {
        return $this->Punishments;
    }

    public function getPunishmentID(string $punishment): int
    {
        return match ($punishment) {
            "Kick" => 1,
            "Ban" => 2,
            default => 0,
        };
    }

    /**
     * @param string $name
     * @return Check|null
     */
    public function getCheckByName(string $name) : ?Check
    {
        foreach ($this->getChecks() as $check){
            if ($check->getName() == $name){
                return $check;
            }
        }
        return null;
    }



}