<?php

namespace Cinematic\cinematic\sequences;


abstract class CinematicSequence
{
    public const MOVE_SEQUENCE = 0;
    public const FADE_SEQUENCE = 1;

    abstract public function start(): void;
}