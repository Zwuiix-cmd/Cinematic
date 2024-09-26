<?php

namespace Cinematic\command;

use Cinematic\entity\CameraEntity;
use Cinematic\Loader;
use Cinematic\session\SessionManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class CinematicSave extends Command
{
    public function __construct()
    {
        parent::__construct("cinematicsave", "Save a cinematic", "", []);
        $this->setPermission(DefaultPermissions::ROOT_OPERATOR);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$sender instanceof Player) return;
        if(count($args) < 1) {
            $sender->sendMessage("§r§cUsage: /cinematicsave name:string");
            return;
        }

        $session = SessionManager::getInstance()->getSession($sender);

        $name = implode(" ", $args);
        Loader::getInstance()->getCinematic()->set($name, array_map(fn(CameraEntity $entity) => $entity->getSettings()->jsonSerialize(), $session->cameraEntity));

        foreach ($session->cameraEntity as $entity) {
            $entity->removeFrom($sender);
        }
        $session->cameraEntity = [];
        $sender->sendMessage("§bYou did save the cinematic with the name {$name}");
    }
}