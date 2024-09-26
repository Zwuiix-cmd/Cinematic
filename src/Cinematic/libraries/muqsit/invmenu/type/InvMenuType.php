<?php

declare(strict_types=1);

namespace Cinematic\libraries\muqsit\invmenu\type;

use Cinematic\libraries\muqsit\invmenu\InvMenu;
use Cinematic\libraries\muqsit\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType{

	public function createGraphic(InvMenu $menu, Player $player) : ?InvMenuGraphic;

	public function createInventory() : Inventory;
}