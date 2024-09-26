<?php

namespace Cinematic\libraries\CameraAPI\Instructions;

use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionColor;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionTime;
use pocketmine\player\Player;

final class FadeCameraInstruction extends CameraInstruction
{
    private ?CameraFadeInstructionTime $time = null;
    private ?CameraFadeInstructionColor $color = null;

    public function setTime(float $fadeInTime, float $stayInTime, float $fadeOutTime): void
    {
        $this->time = new CameraFadeInstructionTime($fadeInTime, $stayInTime, $fadeOutTime);
    }

    /**
     * @return CameraFadeInstructionTime|null
     */
    public function getTime(): ?CameraFadeInstructionTime
    {
        return $this->time;
    }

    public function setColor(float $red, float $green, float $blue): void
    {
        $this->color = new CameraFadeInstructionColor($red, $green, $blue);
    }

    /**
     * @return CameraFadeInstructionColor|null
     */
    public function getColor(): ?CameraFadeInstructionColor
    {
        return $this->color;
    }

    public function send(Player $player): void
    {
        $player->getNetworkSession()->sendDataPacket(CameraInstructionPacket::create(null, null, new CameraFadeInstruction($this->time, $this->color), null, null));
    }
}