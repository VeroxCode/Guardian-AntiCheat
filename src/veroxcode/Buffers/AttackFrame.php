<?php

namespace veroxcode\Buffers;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;

class AttackFrame
{

    /*** @var int */
    private int $ServerTick;
    /*** @var int */
    private int $Ping;
    /*** @var float */
    private float $LastAttack;

    /**
     * @param int $ServerTick
     * @param int $Ping
     * @param float $LastAttack
     */
    public function __construct(int $ServerTick, int $Ping, float $LastAttack)
    {
        $this->ServerTick = $ServerTick;
        $this->Ping = $Ping;
        $this->LastAttack = $LastAttack;
    }

    /**
     * @return int
     */
    public function getServerTick(): int
    {
        return $this->ServerTick;
    }

    /**
     * @return int
     */
    public function getPing(): int
    {
        return $this->Ping;
    }

    /**
     * @return int
     */
    public function getLastAttack(): float
    {
        return $this->LastAttack;
    }


}