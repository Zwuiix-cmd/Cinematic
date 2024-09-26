<?php

namespace Cinematic\cinematic\sequences;

use Cinematic\cinematic\types\CinematicEase;
use Cinematic\libraries\CameraAPI\CameraPresets;
use Cinematic\libraries\CameraAPI\Instructions\SetCameraInstruction;
use Cinematic\session\Session;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;

class CinematicMoveSequence extends CinematicSequence
{
    /**
     * @param Session $session
     * @param Vector3 $position
     * @param Vector2 $rotation
     * @param CinematicEase|null $cinematicEase
     */
    public function __construct(
        protected Session        $session,
        protected Vector3        $position,
        protected Vector2        $rotation,
        protected ?CinematicEase $cinematicEase = null,
    ) {}

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @return Vector3
     */
    public function getPosition(): Vector3
    {
        return $this->position;
    }

    /**
     * @return Vector2
     */
    public function getRotation(): Vector2
    {
        return $this->rotation;
    }

    /**
     * @return CinematicEase|null
     */
    public function getCinematicEase(): ?CinematicEase
    {
        return $this->cinematicEase;
    }

    /**
     * @return void
     */
    public function start(): void
    {
        $camera = new SetCameraInstruction();
        $camera->setPreset(CameraPresets::FREE());
        $camera->setCameraPosition($this->position);
        $camera->setRotation($this->rotation->x, $this->rotation->y);

        if(!is_null($this->cinematicEase)) {
            $camera->setEase($this->cinematicEase->getType(), $this->cinematicEase->getDuration());
        }

        $camera->send($this->session->getPlayer());
    }

    /**
     * @param Session $session
     * @param array $data
     * @return CinematicMoveSequence
     */
    public static function withData(Session $session, array $data): CinematicMoveSequence
    {
        if(!isset($data["position"]) || !isset($data["rotation"])) {
            throw new \Error("Invalid Data");
        }
        $position = $data["position"];
        $rotation = $data["rotation"];
        if(isset($data["ease"])) {
            $ease = new CinematicEase($data["ease"]["type"], $data["ease"]["duration"]);
        } else $ease = null;
        return new CinematicMoveSequence($session, new Vector3($position["x"], $position["y"], $position["z"]), new Vector2($rotation["pitch"], $rotation["yaw"]), $ease);
    }
}