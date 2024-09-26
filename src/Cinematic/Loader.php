<?php

namespace Cinematic;

use Cinematic\command\CinematicEditor;
use Cinematic\command\CinematicLoad;
use Cinematic\command\CinematicSave;
use Cinematic\libraries\CameraAPI\CameraHandler;
use Cinematic\libraries\muqsit\invmenu\InvMenuHandler;
use Cinematic\session\SessionManager;
use JsonException;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use Symfony\Component\Filesystem\Path;

class Loader extends PluginBase
{
    use SingletonTrait;
    protected Config $cinematic;

    protected function onLoad(): void
    {
        self::setInstance($this);
        $this->cinematic = new Config(Path::join($this->getDataFolder(), "cinematic.json"));
    }

    /**
     * @return void
     */
    protected function onEnable(): void
    {
        CameraHandler::register($this);
        InvMenuHandler::register($this);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getCommandMap()->register("", new CinematicEditor());
        $this->getServer()->getCommandMap()->register("", new CinematicSave());
        $this->getServer()->getCommandMap()->register("", new CinematicLoad());
    }

    /**
     * @return void
     * @throws JsonException
     */
    protected function onDisable(): void
    {
        $this->cinematic->save();

        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $session = SessionManager::getInstance()->getSession($player);
            if($session->isInEditor()) {
                $session->setInEditor(false);
            }
        }
    }

    /**
     * @return Config
     */
    public function getCinematic(): Config
    {
        return $this->cinematic;
    }
}