<?php

namespace veroxcode\Buffers;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;

class MovementFrame
{

    /*** @var int */
    private int $ServerTick;
    /*** @var int */
    private int $PlayerTick;
    /*** @var Vector3 */
    private Vector3 $Position;
    /*** @var Vector2 */
    private Vector2 $Rotation;
    /*** @var bool */
    private bool $onGround;
    /*** @var AxisAlignedBB */
    private AxisAlignedBB $BoundingBox;

    /**
     * @param int $ServerTick
     * @param int $PlayerTick
     * @param Vector3 $Position
     * @param Vector2 $Rotation
     * @param bool $onGround
     * @param AxisAlignedBB $BoundingBox
     */
    public function __construct(int $ServerTick, int $PlayerTick, Vector3 $Position, Vector2 $Rotation, bool $onGround, AxisAlignedBB $BoundingBox)
    {
        $this->ServerTick = $ServerTick;
        $this->PlayerTick = $PlayerTick;
        $this->Position = $Position;
        $this->Rotation = $Rotation;
        $this->onGround = $onGround;
        $this->BoundingBox = $BoundingBox;
    }

    /**
     * @return int
     */
    public function getPlayerTick(): int
    {
        return $this->PlayerTick;
    }

    /**
     * @return int
     */
    public function getServerTick(): int
    {
        return $this->ServerTick;
    }

    /**
     * @return bool
     */
    public function isOnGround(): bool
    {
        return $this->onGround;
    }

    /**
     * @return Vector2
     */
    public function getRotation(): Vector2
    {
        return $this->Rotation;
    }

    /**
     * @return Vector3
     */
    public function getPosition(): Vector3
    {
        return $this->Position;
    }

    /**
     * @return AxisAlignedBB
     */
    public function getBoundingBox(): AxisAlignedBB
    {
        return $this->BoundingBox;
    }

}