<?php

namespace Cinematic\entity;

use Cinematic\cinematic\sequences\CinematicSequence;
use Cinematic\cinematic\types\CinematicEase;
use Cinematic\libraries\CameraAPI\Instructions\FadeCameraInstruction;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;

class CameraEntitySettings implements \JsonSerializable
{
    protected ?CinematicEase $cinematicEase = null;
    protected ?FadeCameraInstruction $fadeCameraInstruction = null;

    public function __construct(
        protected Vector3 $position,
        protected Vector2 $rotation,
    ) {}

    /**
     * @return Vector3
     */
    public function getPosition(): Vector3
    {
        return $this->position;
    }

    /**
     * @param Vector3 $pos
     * @return void
     */
    public function setPosition(Vector3 $pos): void
    {
        $this->position = $pos;
    }

    /**
     * @return Vector2
     */
    public function getRotation(): Vector2
    {
        return $this->rotation;
    }

    /**
     * @param Vector2 $rotation
     */
    public function setRotation(Vector2 $rotation): void
    {
        $this->rotation = $rotation;
    }

    /**
     * @return CinematicEase|null
     */
    public function getCinematicEase(): ?CinematicEase
    {
        return $this->cinematicEase;
    }

    /**
     * @param CinematicEase|null $cinematicEase
     */
    public function setCinematicEase(?CinematicEase $cinematicEase): void
    {
        $this->cinematicEase = $cinematicEase;
    }

    /**
     * @return FadeCameraInstruction|null
     */
    public function getFadeCameraInstruction(): ?FadeCameraInstruction
    {
        return $this->fadeCameraInstruction;
    }

    /**
     * @param FadeCameraInstruction|null $fadeCameraInstruction
     */
    public function setFadeCameraInstruction(?FadeCameraInstruction $fadeCameraInstruction): void
    {
        $this->fadeCameraInstruction = $fadeCameraInstruction;
    }

    public function jsonSerialize(): array
    {
        $data = [
            "position" => ["x" => $this->position->getX(), "y" => $this->position->getY(), "z" => $this->position->getZ()],
            "rotation" => ["yaw" => $this->rotation->getX(), "pitch" => $this->rotation->getY()]
        ];

        if(!is_null($this->getFadeCameraInstruction())) {
            $fadeInstruction = $this->getFadeCameraInstruction();
            $color = $fadeInstruction->getColor();
            $time = $fadeInstruction->getTime();

            $data["color"] = ["r" => $color->getRed(), "g" => $color->getGreen(), "b" => $color->getBlue()];
            $data["time"] = ["fadeInTime" => $time->getFadeInTime(), "stayTime" => $time->getStayTime(), "fadeOutTime" => $time->getFadeOutTime()];

            $data["type"] = CinematicSequence::FADE_SEQUENCE;
        } elseif(!is_null($this->getCinematicEase())) {
            $data["type"] = CinematicSequence::MOVE_SEQUENCE;
            $data["ease"] = ["type" => $this->getCinematicEase()->getType(), "duration" => $this->getCinematicEase()->getDuration()];
        } else $data["type"] = CinematicSequence::MOVE_SEQUENCE;

        return $data;
    }
}