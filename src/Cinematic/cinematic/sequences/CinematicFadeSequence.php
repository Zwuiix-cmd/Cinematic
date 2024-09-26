<?php

namespace Cinematic\cinematic\sequences;


use Cinematic\cinematic\types\CinematicEase;
use Cinematic\libraries\CameraAPI\Instructions\FadeCameraInstruction;
use Cinematic\session\Session;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;

class CinematicFadeSequence extends CinematicSequence
{
    /**
     * @param Session $session
     * @param FadeCameraInstruction $instruction
     */
    public function __construct(
        protected Session $session,
        protected FadeCameraInstruction $instruction,
    ) {}

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @return FadeCameraInstruction
     */
    public function getInstruction(): FadeCameraInstruction
    {
        return $this->instruction;
    }

    /**
     * @return void
     */
    public function start(): void
    {
        if(!$this->session->getPlayer()->isConnected()) return;
        $this->instruction->send($this->session->getPlayer());
    }

    /**
     * @param Session $session
     * @param array $data
     * @return CinematicFadeSequence
     */
    public static function withData(Session $session, array $data): CinematicFadeSequence
    {
        if(!isset($data["time"]) || !isset($data["color"])) {
            throw new \Error("Invalid Data");
        }

        $instruction = new FadeCameraInstruction();
        $instruction->setTime($data["time"]["fadeInTime"], $data["time"]["stayTime"], $data["time"]["fadeOutTime"]);
        $instruction->setColor($data["color"]["r"], $data["color"]["b"], $data["color"]["g"]);
        return new CinematicFadeSequence($session, $instruction);
    }
}