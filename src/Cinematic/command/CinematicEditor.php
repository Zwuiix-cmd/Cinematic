<?php

namespace Cinematic\command;

use Cinematic\libraries\CameraAPI\Instructions\AimAssistCameraInstruction;
use Cinematic\session\SessionManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\types\camera\CameraAimAssistActionType;
use pocketmine\network\mcpe\protocol\types\camera\CameraAimAssistTargetMode;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class CinematicEditor extends Command
{
    public function __construct()
    {
        parent::__construct("cinematiceditor", "Edit a cinematic", "", []);
        $this->setPermission(DefaultPermissions::ROOT_OPERATOR);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$sender instanceof Player) return;
        $session = SessionManager::getInstance()->getSession($sender);
        $session->setInEditor(!$session->isInEditor());
    }
}