<?php

namespace Olympia\Kitpvp\entities\boxs;

use Olympia\Kitpvp\managers\types\BoxsManager;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;

class EpicBox extends Box
{
    public function __construct(Location $location, CompoundTag $nbt)
    {
        parent::__construct($location, $nbt);
        $this->setNameTag("§dEpique");
    }

    public static function getNetworkTypeId(): string
    {
        return "olympia:epic_box";
    }

    public function getName(): string
    {
        return "EpiqueBox";
    }

    public function getKey(): string
    {
        return BoxsManager::getInstance()->getKeyName(BoxsManager::BOX_EPIC);
    }
}