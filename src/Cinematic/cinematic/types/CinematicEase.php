<?php

namespace Cinematic\cinematic\types;

final class CinematicEase
{
    public function __construct(
        protected int $type,
        protected float $duration,
    ) {}

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
    }
}