<?php

namespace Cinematic\command;

use Cinematic\api\CinematicAPI;
use Cinematic\cinematic\Cinematic;
use Cinematic\cinematic\sequences\CinematicMoveSequence;
use Cinematic\Loader;
use Cinematic\session\SessionManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class CinematicLoad extends Command
{
    public function __construct()
    {
        parent::__construct("cinematicload", "Load a cinematic", "", []);
        $this->setPermission(DefaultPermissions::ROOT_OPERATOR);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     * @throws \ReflectionException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$sender instanceof Player) return;
        if(count($args) < 1) {
            $sender->sendMessage("§r§cUsage: /cinematicload name:string");
            return;
        }

        $name = implode(" ", $args);
        if(!CinematicAPI::exist($name)) {
            $sender->sendMessage("§cImpossible to find cinematic.");
            return;
        }

        $cinematic = CinematicAPI::loadCinematic($sender, $name);
        $cinematic->start();
    }
}