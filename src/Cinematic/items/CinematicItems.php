<?php

namespace Cinematic\items;

use pocketmine\block\utils\DyeColor;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

class CinematicItems
{
    /**
     * @return Item
     */
    public static function START(): Item
    {
        return VanillaItems::DYE()->setColor(DyeColor::CYAN())->setCustomName("§r§bStart");
    }

    /**
     * @return Item
     */
    public static function PAUSE(): Item
    {
        return VanillaItems::DYE()->setColor(DyeColor::GRAY())->setCustomName("§r§bPause");
    }

    /**
     * @return Item
     */
    public static function STOP(): Item
    {
        return VanillaItems::DYE()->setColor(DyeColor::RED())->setCustomName("§r§bStop");
    }

    /**
     * @return Item
     */
    public static function REMOVE(): Item
    {
        return VanillaItems::NAUTILUS_SHELL()->setCustomName("§r§bRemove Point");
    }

    /**
     * @return Item
     */
    public static function SETTINGS(): Item
    {
        return VanillaItems::EMERALD()->setCustomName("§r§bSettings");
    }

    /**
     * @return Item
     */
    public static function ADD_POINT(): Item
    {
        return VanillaItems::NAME_TAG()->setCustomName("§r§bCreate new Point");
    }

    /**
     * @return Item
     */
    public static function CHANGE_ANGLE(): Item
    {
        return VanillaItems::STICK()->setCustomName("§r§bChange angle of point");
    }
}