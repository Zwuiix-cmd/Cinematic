<?php

namespace Cinematic\cinematic;

use Cinematic\cinematic\sequences\CinematicFadeSequence;
use Cinematic\cinematic\sequences\CinematicMoveSequence;
use Cinematic\cinematic\sequences\CinematicSequence;
use Cinematic\libraries\CameraAPI\Instructions\ClearCameraInstruction;
use Cinematic\Loader;
use Cinematic\session\Session;
use pocketmine\entity\Location;
use pocketmine\network\mcpe\protocol\SetHudPacket;
use pocketmine\network\mcpe\protocol\types\hud\HudElement;
use pocketmine\network\mcpe\protocol\types\hud\HudVisibility;
use pocketmine\player\GameMode;
use pocketmine\scheduler\ClosureTask;
use ReflectionException;

class Cinematic
{
    /**
     * @var CinematicSequence[]
     */
    protected array $sequence = [];
    protected bool $started = false;
    protected bool $pause = false;
    protected int $time = 0;
    protected GameMode $oldGamemode;

    public function __construct(
        protected Session $session,
    ) {
    }

    /**
     * @return int
     */
    public function getNextPriority(): int
    {
        return count($this->sequence);
    }

    /**
     * @param int $priority
     * @param CinematicSequence $sequence
     * @return void
     */
    public function addSequence(int $priority, CinematicSequence $sequence): void
    {
        $this->sequence[$priority] = $sequence;
    }

    /**
     * @return CinematicSequence[]
     */
    public function getSequences(): array
    {
        return $this->sequence;
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function start(): void
    {
        $this->started = true;
        $this->session->setCinematic($this);

        $this->oldGamemode = $this->session->getPlayer()->getGamemode();
        $this->session->getPlayer()->setGamemode(GameMode::ADVENTURE());
        $this->session->getPlayer()->getNetworkSession()->sendDataPacket(SetHudPacket::create([
            HudElement::PAPER_DOLL,
            HudElement::ARMOR,
            HudElement::TOOLTIPS,
            HudElement::TOUCH_CONTROLS,
            HudElement::CROSSHAIR,
            HudElement::HOTBAR,
            HudElement::HEALTH,
            HudElement::XP,
            HudElement::FOOD,
            HudElement::AIR_BUBBLES,
            HudElement::HORSE_HEALTH,
            HudElement::STATUS_EFFECTS,
            HudElement::ITEM_TEXT,
        ], HudVisibility::HIDE));

        foreach ($this->session->cameraEntity as $entity) {
            $entity->removeFrom($this->session->getPlayer());
        }

        $this->nextSequence();
    }

    /**
     * @return void
     */
    public function stop(): void
    {
        $this->started = false;
        if(!$this->session->getPlayer()->isConnected()) return;
        $this->session->setCinematic(null);

        (new ClearCameraInstruction())->send($this->session->getPlayer());
        $this->session->getPlayer()->setGamemode($this->oldGamemode);

        $this->session->getPlayer()->getNetworkSession()->sendDataPacket(SetHudPacket::create([
            HudElement::PAPER_DOLL,
            HudElement::ARMOR,
            HudElement::TOOLTIPS,
            HudElement::TOUCH_CONTROLS,
            HudElement::CROSSHAIR,
            HudElement::HOTBAR,
            HudElement::HEALTH,
            HudElement::XP,
            HudElement::FOOD,
            HudElement::AIR_BUBBLES,
            HudElement::HORSE_HEALTH,
            HudElement::STATUS_EFFECTS,
            HudElement::ITEM_TEXT,
        ], HudVisibility::RESET));

        foreach ($this->session->cameraEntity as $entity) {
            $entity->spawnTo($this->session->getPlayer());
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function nextSequence(): void
    {
        if($this->pause) return;

        $sequence = $this->sequence[$this->time] ?? null;
        if(!$sequence instanceof CinematicSequence) {
            $this->stop();
            return;
        }
        $sequence->start();
        $this->time++;

        if($sequence instanceof CinematicMoveSequence) {
            $delay = is_null($sequence->getCinematicEase()) ? 0 : $sequence->getCinematicEase()->getDuration() * 20;
        } elseif($sequence instanceof CinematicFadeSequence) {
            $delay = round($sequence->getInstruction()->getTime()->getFadeOutTime());
        } else $delay = 0;
        if($delay === 0) {
            $this->nextSequence();
        } else  Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(fn() => $this->nextSequence()), $delay);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function pause(): void
    {
        $this->pause = !$this->pause;
        if(!$this->pause) {
            $this->nextSequence();
        }
    }
}