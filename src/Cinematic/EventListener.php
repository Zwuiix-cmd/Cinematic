<?php

namespace Cinematic;

use Cinematic\cinematic\Cinematic;
use Cinematic\cinematic\sequences\CinematicFadeSequence;
use Cinematic\cinematic\sequences\CinematicMoveSequence;
use Cinematic\cinematic\sequences\CinematicSequence;
use Cinematic\entity\CameraEntity;
use Cinematic\items\CinematicItems;
use Cinematic\session\SessionManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\player\Player;

class EventListener implements Listener
{
    /**
     * @param PlayerLoginEvent $ev
     * @return void
     */
    public function onLogin(PlayerLoginEvent $ev): void
    {
        SessionManager::getInstance()->addSession($ev->getPlayer());
    }

    /**
     * @param PlayerQuitEvent $ev
     * @return void
     */
    public function onQuit(PlayerQuitEvent $ev): void
    {
        SessionManager::getInstance()->removeSession($ev->getPlayer());
    }

    /**
     * @param PlayerItemUseEvent $event
     * @return void
     * @throws \ReflectionException
     */
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $session = SessionManager::getInstance()->getSession($event->getPlayer());

        if(!$session->isInEditor()) return;
        $event->cancel();
        if($item->equals(CinematicItems::START())) {
            if($session->isInCinematic()) {
                $session->getPlayer()->sendMessage("§cYou're already in a cinematic.");
                return;
            }

            $cinematic = new Cinematic($session);
            foreach ($session->cameraEntity as $entity) {
                $data = $entity->getSettings()->jsonSerialize();
                $class = match ($data["type"] ?? -1) {
                    CinematicSequence::MOVE_SEQUENCE => CinematicMoveSequence::class,
                    CinematicSequence::FADE_SEQUENCE => CinematicFadeSequence::class,
                    default => throw new \Error("Impossible to find sequence type")
                };

                $cinematic->addSequence($cinematic->getNextPriority(), $class::withData($session, $data));
            }
            $cinematic->start();
        } else if($item->equals(CinematicItems::STOP())) {
            if (!$session->isInCinematic()) {
                $session->getPlayer()->sendMessage("§cYou're not in a cinematic.");
                return;
            }
            $session->getCinematic()?->stop();
        } else if($item->equals(CinematicItems::PAUSE())) {
            if (!$session->isInCinematic()) {
                $session->getPlayer()->sendMessage("§cYou're not in a cinematic.");
                return;
            }

            $session->getCinematic()?->pause();
        } else if($item->equals(CinematicItems::ADD_POINT())) {
            $session->addCameraEntity();
        } else if($item->equals(CinematicItems::CHANGE_ANGLE())) {
            $session->getPlayer()->sendMessage("§cAttack the point you wish to modify in angle or position.");
        }
    }

    /**
     * @param DataPacketReceiveEvent $ev
     */
    public function handleReceivePacket(DataPacketReceiveEvent $ev): void
    {
        $packet = $ev->getPacket();

        $player = ($origin = $ev->getOrigin())->getPlayer();
        if(!$player instanceof Player) return;

        $session = SessionManager::getInstance()->getSession($player);
        if(!$session->isInEditor()) return;

        if($packet instanceof InventoryTransactionPacket) {
            $data = $packet->trData;
            if($data instanceof UseItemOnEntityTransactionData) {
                if($data->getActionType() === UseItemOnEntityTransactionData::ACTION_ATTACK) {
                    $item = $player->getInventory()->getItemInHand();
                    if($item->equals(CinematicItems::CHANGE_ANGLE())) {
                        $session->changeAngle($data->getActorRuntimeId());
                    } elseif($item->equals(CinematicItems::SETTINGS())) {
                        $session->openSettings($data->getActorRuntimeId());
                    } elseif($item->equals(CinematicItems::REMOVE())) {
                        $entity = $session->cameraEntity[$data->getActorRuntimeId()] ?? null;
                        if($entity instanceof CameraEntity) {
                            $entity->removeFrom($player);
                            unset($session->cameraEntity[$data->getActorRuntimeId()]);
                        }
                    }
                }
            }
        }
    }
}