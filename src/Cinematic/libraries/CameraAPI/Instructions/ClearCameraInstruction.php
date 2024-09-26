<?php

namespace Cinematic\libraries\CameraAPI\Instructions;

use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\player\Player;

final class ClearCameraInstruction extends CameraInstruction
{
    private ?bool $clear = true;
    private ?bool $removeTarget = true;

    public function setClear(bool $clear): void
    {
        $this->clear = $clear;
    }

    public function setRemoveTarget(bool $removeTarget): void
    {
        $this->removeTarget = $removeTarget;
    }

    public function send(Player $player): void
    {
        if(!$player->isConnected()) return;
        $player->getNetworkSession()->sendDataPacket(CameraInstructionPacket::create(null, $this->clear, null, null, $this->removeTarget));
    }
}
