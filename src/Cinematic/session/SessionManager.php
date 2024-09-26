<?php

namespace Cinematic\session;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class SessionManager
{
    use SingletonTrait;

    /**
     * @var Session[]
     */
    protected array $sessions = [];

    /**
     * @param Player $player
     * @return Session
     */
    public function getSession(Player $player): Session
    {
        return $this->sessions[$key = $player->getUniqueId()->getBytes()] ?? $this->sessions[$key] = new Session($player);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function addSession(Player $player): void
    {
        $this->sessions[$player->getUniqueId()->getBytes()] = new Session($player);
    }

    public function removeSession(Player $player): void
    {
        if(isset($this->sessions[$key = $player->getUniqueId()->getBytes()])) {
            $this->sessions[$key]->setInEditor(false);
            unset($this->sessions[$key]);
        }
    }
}