<?php

namespace Cinematic\entity;

use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeMap;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;

class CameraEntity
{
    protected int $entityId;
    protected EntityMetadataCollection $networkProperties;
    protected AttributeMap $attributeMap;
    protected string $nameTag = "";
    public CameraEntitySettings $settings;

    public function __construct(protected Location $location) {
        $this->entityId = Entity::nextRuntimeId();
        $this->networkProperties = new EntityMetadataCollection();
        $this->attributeMap = new AttributeMap();
        $this->settings = new CameraEntitySettings($this->location->asVector3()->floor(), (new Vector2($this->location->yaw, $this->location->pitch))->floor());
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getNameTag(): string
    {
        return $this->nameTag;
    }

    /**
     * @param string $nameTag
     * @return void
     */
    public function setNameTag(string $nameTag): void
    {
        $this->nameTag = $nameTag;
        $this->syncData();
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @param Player $player
     * @return void
     */
    public function spawnTo(Player $player): void
    {
        if(!$player->isConnected()) return;
        $player->getNetworkSession()->sendDataPacket(AddActorPacket::create(
            $this->getId(), //TODO: actor unique ID
            $this->getId(),
            EntityIds::ALLAY,
            $this->location->asVector3(),
            new Vector3(0, 0, 0),
            $this->location->pitch,
            $this->location->yaw,
            $this->location->yaw, //TODO: head yaw
            $this->location->yaw, //TODO: body yaw (wtf mojang?)
            array_map(function(Attribute $attr) : NetworkAttribute{
                return new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue(), []);
            }, $this->attributeMap->getAll()),
            $this->networkProperties->getAll(),
            new PropertySyncData([], []),
            [] //TODO: entity links
        ));
        $this->syncData();
    }

    /**
     * @param float $yaw
     * @param float $pitch
     * @return void
     */
    public function updateRotation(float $yaw, float $pitch): void
    {
        $this->location = Location::fromObject($this->location->asVector3(), $this->location->getWorld(), $yaw, $pitch);
    }

    public function sendMovementTo(Player $player): void
    {
        if(!$player->isConnected()) return;
        $player->getNetworkSession()->sendDataPacket(MoveActorAbsolutePacket::create(
            $this->getId(),
            $this->location,
            $this->location->pitch,
            $this->location->yaw,
            $this->location->yaw,
            (0)
        ));
    }

    /**
     * @param Player $player
     * @return void
     */
    public function sendData(Player $player): void
    {
        if(!$player->isConnected()) return;
        $properties = $this->networkProperties->getAll();
        ksort($properties, SORT_NUMERIC);
        $player->getNetworkSession()->sendDataPacket(SetActorDataPacket::create($this->getId(), $properties, new PropertySyncData([], []), 0));

    }

    /**
     * @param Player $player
     * @return void
     */
    public function removeFrom(Player $player): void
    {
        if(!$player->isConnected()) return;
        $player->getNetworkSession()->sendDataPacket(RemoveActorPacket::create($this->getId()));
    }

    /**
     * @param EntityMetadataCollection $properties
     * @return void
     */
    protected function syncNetworkData(EntityMetadataCollection $properties) : void{
        $properties->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, $this->nameTag !== "" ? 1 : 0);
        $properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0.3);
        $properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.3);
        $properties->setFloat(EntityMetadataProperties::SCALE, 1);
        $properties->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
        $properties->setLong(EntityMetadataProperties::OWNER_EID, $this->ownerId ?? -1);
        $properties->setLong(EntityMetadataProperties::TARGET_EID, $this->targetId ?? 0);
        $properties->setString(EntityMetadataProperties::NAMETAG, $this->nameTag);

        $properties->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, false);
        $properties->setGenericFlag(EntityMetadataFlags::CAN_CLIMB, false);
        $properties->setGenericFlag(EntityMetadataFlags::CAN_SHOW_NAMETAG, true);
        $properties->setGenericFlag(EntityMetadataFlags::HAS_COLLISION, false);
        $properties->setGenericFlag(EntityMetadataFlags::NO_AI, false);
        $properties->setGenericFlag(EntityMetadataFlags::INVISIBLE, false);
        $properties->setGenericFlag(EntityMetadataFlags::SILENT, true);
        $properties->setGenericFlag(EntityMetadataFlags::ONFIRE, false);
        $properties->setGenericFlag(EntityMetadataFlags::WALLCLIMBING, false);
    }

    /**
     * @return void
     */
    public function syncData(): void
    {
        $this->syncNetworkData($this->networkProperties);
    }

    /**
     * @return CameraEntitySettings
     */
    public function getSettings(): CameraEntitySettings
    {
        $location = $this->getLocation();
        $this->settings->setPosition($location->floor());
        $this->settings->setRotation((new Vector2($location->yaw, $location->pitch))->floor());
        return $this->settings;
    }
}