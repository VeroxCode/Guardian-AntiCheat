<?php

namespace veroxcode\User;

use veroxcode\Buffers\MovementFrame;
use veroxcode\Guardian;
use veroxcode\Utils\Arrays;
use veroxcode\Utils\Random;

class User
{

    private CONST MOVEMENT_BUFFER_SIZE = 100;

    private string $uuid;

    private array $movementBuffer = [];
    private array $violations = [];

    /**
     * @param string $uuid
     */
    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;

        foreach (Guardian::getInstance()->getCheckManager()->getChecks() as $Check){
            $this->violations[$Check->getName()] = 0;
        }
    }

    public function getUUID(): string
    {
        return $this->uuid;
    }

    public function addToMovementBuffer(MovementFrame $object) : void
    {
        $size = count($this->movementBuffer);

        if ($size >= ($this::MOVEMENT_BUFFER_SIZE)){
            $this->movementBuffer = Arrays::removeFirst($this->movementBuffer);
        }
        $this->movementBuffer[$size] = $object;
    }

    public function rewindMovementBuffer(int $ticks = 1) : MovementFrame
    {
        $size = count($this->movementBuffer) - 1;
        return $this->movementBuffer[$size - $ticks];
    }

    public function getMovementBuffer(): array
    {
        return $this->movementBuffer;
    }

    public function increaseViolation(string $Check, $amount) : void
    {
        $this->violations[$Check] = Random::clamp(0, PHP_INT_MAX, $this->violations[$Check] + $amount);
    }

    public function decreaseViolation(string $Check, $amount) : void
    {
        $this->violations[$Check] = Random::clamp(0, PHP_INT_MAX, $this->violations[$Check] - $amount);
    }

    public function resetViolation(string $Check) : void
    {
        $this->violations[$Check] = 0;
    }

    public function getViolation(string $Check) : int
    {
        return $this->violations[$Check];
    }


}