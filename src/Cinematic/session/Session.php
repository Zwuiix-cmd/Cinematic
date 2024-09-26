<?php

namespace Cinematic\session;

use Cinematic\cinematic\Cinematic;
use Cinematic\cinematic\types\CinematicEase;
use Cinematic\entity\CameraEntity;
use Cinematic\items\CinematicItems;
use Cinematic\libraries\CameraAPI\Instructions\FadeCameraInstruction;
use Cinematic\libraries\jojoe77777\FormAPI\CustomForm;
use Cinematic\libraries\jojoe77777\FormAPI\SimpleForm;
use Cinematic\libraries\muqsit\invmenu\InvMenu;
use Cinematic\libraries\muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\entity\Location;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstruction;
use pocketmine\player\GameMode;
use pocketmine\player\Player;

class Session
{
    protected ?Cinematic $cinematic = null;
    protected bool $inEditor = false;
    /**
     * @var CameraEntity[]
     */
    public array $cameraEntity = [];
    
    public function __construct(protected Player $player)
    {
    }

    /**
     * @return bool
     */
    public function isInEditor(): bool
    {
        return $this->inEditor;
    }

    /**
     * @param bool $inEditor
     */
    public function setInEditor(bool $inEditor): void
    {
        $this->inEditor = $inEditor;

        if(!$inEditor) {
            $this->getPlayer()->getInventory()->clearAll();
            $this->getPlayer()->getArmorInventory()->clearAll();
            $this->getPlayer()->getCursorInventory()->clearAll();
            $this->getPlayer()->getOffHandInventory()->clearAll();

            $this->getPlayer()->setFlying(false);
            $this->getPlayer()->setAllowFlight(false);
            $this->getPlayer()->setHasBlockCollision(true);

            $this->getPlayer()->setGamemode(GameMode::SURVIVAL());

            foreach ($this->cameraEntity as $entity) {
                $entity->removeFrom($this->getPlayer());
            }
            return;
        }

        $this->getPlayer()->setFlying(true);
        $this->getPlayer()->setAllowFlight(true);
        $this->getPlayer()->setHasBlockCollision(false);
        $this->getPlayer()->setGamemode(GameMode::CREATIVE());

        $this->getPlayer()->getInventory()->setContents([
            0 => CinematicItems::START(),
            1 => CinematicItems::PAUSE(),
            2 => CinematicItems::STOP(),
            4 => CinematicItems::ADD_POINT(),
            5 => CinematicItems::REMOVE(),
            6 => CinematicItems::CHANGE_ANGLE(),
            8 => CinematicItems::SETTINGS()
        ]);
    }

    /**
     * @return bool
     */
    public function isInCinematic(): bool
    {
        return $this->cinematic != null;
    }

    /**
     * @return Cinematic|null
     */
    public function getCinematic(): ?Cinematic
    {
        return $this->cinematic;
    }

    /**
     * @param Cinematic|null $cinematic
     */
    public function setCinematic(?Cinematic $cinematic): void
    {
        $this->cinematic = $cinematic;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return void
     */
    public function addCameraEntity(): void
    {
        $location = $this->getPlayer()->getLocation();
        $entity = new CameraEntity(Location::fromObject($location->asVector3()->add(0, 1.62, 0), $location->getWorld(), $location->getYaw(), $location->getPitch()));

        $nameTag = count($this->cameraEntity);
        if(count($this->cameraEntity) < 1) $nameTag = "Start";

        $entity->setNameTag("§r§b{$nameTag}");
        $entity->spawnTo($this->getPlayer());

        $this->cameraEntity[$entity->getId()] = $entity;
    }

    /**
     * @param int $actorRuntimeId
     * @return void
     */
    public function changeAngle(int $actorRuntimeId): void
    {
        $cameraEntity = $this->cameraEntity[$actorRuntimeId] ?? null;
        if(!$cameraEntity instanceof CameraEntity) return;

        $rotation = $this->getPlayer()->getLocation();
        $yaw = floor($rotation->getYaw());
        $pitch = floor($rotation->getPitch());
        $cameraEntity->updateRotation($yaw, $pitch);
        $cameraEntity->sendMovementTo($this->getPlayer());
        $this->getPlayer()->sendMessage("§bYou have correctly changed the camera rotation ({$cameraEntity->getId()}) to yaw=$yaw pitch=$pitch");
    }

    public function openSettings(int $actorRuntimeId): void
    {
        $cameraEntity = $this->cameraEntity[$actorRuntimeId] ?? null;
        if(!$cameraEntity instanceof CameraEntity) return;
        $location = $cameraEntity->getLocation();

        $form = new SimpleForm(function (Player $player, mixed $data) use($cameraEntity) {
            if(is_null($data)) return;
            $settings = $cameraEntity->getSettings();
            switch ($data) {
                case "nametag":
                    $form = new CustomForm(function (Player $player, mixed $data) use($cameraEntity) {
                        if(is_null($data)) return;
                        $cameraEntity->setNameTag($data["nametag"]);
                    });
                    $form->setTitle("Change NameTag");
                    $form->addInput("NameTag", $cameraEntity->getNameTag(), $cameraEntity->getNameTag(), "nametag");
                    $player->sendForm($form);
                    break;
                case "ease":
                    if(is_null($settings->getCinematicEase())) {
                        $form = new SimpleForm(function (Player $player, mixed $data) use($cameraEntity) {
                            if(is_null($data)) return;
                            $cameraEntity->settings->setCinematicEase(new CinematicEase(0, 5));
                            $player->sendForm($this->loadSettingsEase($cameraEntity));
                        });
                        $form->setTitle("Create a cinematic ease ?");
                        $form->addButton("Click for create", label: "create");
                    } else {
                        $form = new SimpleForm(function (Player $player, mixed $data) use($cameraEntity) {
                            if(is_null($data)) return;
                            if($data == "edit") {
                                $player->sendForm($this->loadSettingsEase($cameraEntity));
                            } else $cameraEntity->settings->setCinematicEase(null);
                        });
                        $form->setTitle("Remove or a edit (ease)");
                        $form->addButton("Edit", label: "edit");
                        $form->addButton("Remove", label: "remove");
                    }
                    $player->sendForm($form);
                    break;
                case "fade":
                    if(is_null($settings->getFadeCameraInstruction())) {
                        $form = new SimpleForm(function (Player $player, mixed $data) use($cameraEntity) {
                            if(is_null($data)) return;
                            $instruction = new FadeCameraInstruction();
                            $instruction->setColor(0, 0, 0);
                            $instruction->setTime(1, 1, 1);
                            $cameraEntity->settings->setFadeCameraInstruction($instruction);
                            $player->sendForm($this->loadSettingsFade($cameraEntity));
                        });
                        $form->setTitle("Create a cinematic fade ?");
                        $form->addButton("Click for create", label: "create");
                    } else {
                        $form = new SimpleForm(function (Player $player, mixed $data) use($cameraEntity) {
                            if(is_null($data)) return;
                            if($data == "edit") {
                                $player->sendForm($this->loadSettingsFade($cameraEntity));
                            } else $cameraEntity->settings->setFadeCameraInstruction(null);
                        });
                        $form->setTitle("Remove or a edit (fade)");
                        $form->addButton("Edit", label: "edit");
                        $form->addButton("Remove", label: "remove");
                    }
                    $player->sendForm($form);
                    break;
            }
        });
        $form->setTitle("Settings ({$cameraEntity->getNameTag()})");
        $form->addButton("Change NameTag", label: "nameTag");
        $form->addButton("Configure Ease", label: "ease");
        $form->addButton("Configure Fade", label: "fade");

        $this->getPlayer()->sendForm($form);
    }

    /**
     * @param CameraEntity $cameraEntity
     * @return CustomForm
     */
    public function loadSettingsEase(CameraEntity $cameraEntity): CustomForm
    {
        $settings = $cameraEntity->getSettings();
        $ease = $settings->getCinematicEase();
        $form = new CustomForm(function (Player $player, mixed $data) use($cameraEntity) {
            if(is_null($data)) return;
            $cameraEntity->getSettings()->setCinematicEase(new CinematicEase($data["type"], $data["duration"]));
        });
        $form->setTitle("Configure Ease");
        $form->addInput("Type", $ease->getType(), $ease->getType(), "type");
        $form->addInput("Duration", $ease->getDuration(), $ease->getDuration(), "duration");
        return $form;
    }

    /**
     * @param CameraEntity $cameraEntity
     * @return CustomForm
     */
    public function loadSettingsFade(CameraEntity $cameraEntity): CustomForm
    {
        $settings = $cameraEntity->getSettings();
        $fade = $settings->getFadeCameraInstruction();
        $form = new CustomForm(function (Player $player, mixed $data) use($cameraEntity) {
            if(is_null($data)) return;
            $instruction = new FadeCameraInstruction();
            $instruction->setColor($data["r"], $data["g"], $data["b"]);
            $instruction->setTime($data["fadeInTime"], $data["stayTime"], $data["fadeOutTime"]);
            $cameraEntity->getSettings()->setFadeCameraInstruction($instruction);
        });
        $form->setTitle("Configure Fade");
        $form->addSlider("Color R", 0, 255, -1, $fade->getColor()->getRed(), "r");
        $form->addSlider("Color G", 0, 255, -1, $fade->getColor()->getGreen(), "g");
        $form->addSlider("Color B", 0, 255, -1, $fade->getColor()->getBlue(), "b");

        $time = $fade->getTime();
        $form->addSlider("Time", 0, 500,-1, $time->getFadeInTime(), "fadeInTime");
        $form->addSlider("Time", 0, 500,-1, $time->getStayTime(), "stayTime");
        $form->addSlider("Time", 0, 500,-1, $time->getFadeOutTime(), "fadeOutTime");
        return $form;
    }
}