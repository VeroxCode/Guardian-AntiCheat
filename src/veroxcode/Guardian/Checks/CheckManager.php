<?php

namespace veroxcode\Guardian\Checks;

use veroxcode\Guardian\Checks\Combat\AutoClicker;
use veroxcode\Guardian\Checks\Combat\Hitbox;
use veroxcode\Guardian\Checks\Combat\ImpossibleRotations;
use veroxcode\Guardian\Checks\Combat\Reach;
use veroxcode\Guardian\Checks\Movement\Timer;
use veroxcode\Guardian\Checks\Packets\BadPacketsA;
use veroxcode\Guardian\Checks\World\GhostHand;

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