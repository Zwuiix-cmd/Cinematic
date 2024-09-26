<?php

namespace Cinematic\api;

use Cinematic\cinematic\Cinematic;
use Cinematic\cinematic\sequences\CinematicFadeSequence;
use Cinematic\cinematic\sequences\CinematicMoveSequence;
use Cinematic\cinematic\sequences\CinematicSequence;
use Cinematic\Loader;
use Cinematic\session\SessionManager;
use http\Exception;
use pocketmine\player\Player;

class CinematicAPI
{
    /**
     * @param string $name
     * @return bool
     */
    public static function exist(string $name): bool
    {
        return Loader::getInstance()->getCinematic()->exists($name);
    }

    /**
     * @param Player $player
     * @param string $name
     * @return Cinematic
     */
    public static function loadCinematic(Player $player, string $name): Cinematic
    {
        $session = SessionManager::getInstance()->getSession($player);
        $cinematic = new Cinematic($session);
        foreach (self::loadSequences($name) as $id => $data) {
            $class = match ($data["type"] ?? -1) {
                CinematicSequence::MOVE_SEQUENCE => CinematicMoveSequence::class,
                CinematicSequence::FADE_SEQUENCE => CinematicFadeSequence::class,
                default => throw new \Error("Impossible to find sequence type")
            };

            $cinematic->addSequence($id, $class::withData($session, $data));
        }
        return $cinematic;
    }

    /**
     * @param string $name
     * @return array
     */
    public static function loadSequences(string $name): array
    {
        return Loader::getInstance()->getCinematic()->get($name, []);
    }
}