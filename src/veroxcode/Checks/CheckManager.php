<?php

namespace veroxcode\Checks;

use veroxcode\Checks\Combat\AutoClicker;
use veroxcode\Checks\Combat\Hitbox;
use veroxcode\Checks\Combat\ImpossibleRotations;
use veroxcode\Checks\Combat\Reach;
use veroxcode\Checks\Movement\Timer;
use veroxcode\Checks\Packets\BadPacketsA;
use veroxcode\Checks\World\GhostHand;

class CheckManager
{

    /**
     * @var Check[]
     */
    public array $Checks = [];

    public function __construct()
    {
        $this->Checks[] = new Reach();
        $this->Checks[] = new Hitbox();
        $this->Checks[] = new Timer();
        $this->Checks[] = new AutoClicker();
        $this->Checks[] = new BadPacketsA();
        $this->Checks[] = new GhostHand();
        $this->Checks[] = new ImpossibleRotations();
    }

    /**
     * @return Check[]
     */
    public function getChecks() : array
    {
        return $this->Checks;
    }

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