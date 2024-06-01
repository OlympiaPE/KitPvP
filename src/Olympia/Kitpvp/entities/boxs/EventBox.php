<?php

namespace Olympia\Kitpvp\entities\boxs;

use Olympia\Kitpvp\managers\types\BoxsManager;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;

class EventBox extends Box
{
    public function __construct(Location $location, CompoundTag $nbt)
    {
        parent::__construct($location, $nbt);
        $this->setNameTag("§bEvent");
    }

    public static function getNetworkTypeId(): string
    {
        return "olympia:event_box";
    }

    public function getName(): string
    {
        return "EventBox";
    }

    public function getKey(): string
    {
        return BoxsManager::getInstance()->getKeyName(BoxsManager::BOX_EVENT);
    }
}