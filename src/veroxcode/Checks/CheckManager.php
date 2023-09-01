<?php

namespace veroxcode\Checks;

use veroxcode\Checks\Combat\Hitbox;
use veroxcode\Checks\Combat\Reach;

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
    }

    /**
     * @return Check[]
     */
    public function getChecks() : array
    {
        return $this->Checks;
    }

}