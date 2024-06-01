<?php

namespace Olympia\Kitpvp\entities\boxs;

use Olympia\Kitpvp\managers\types\BoxsManager;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;

class ShopBox extends Box
{
    public function __construct(Location $location, CompoundTag $nbt)
    {
        parent::__construct($location, $nbt);
        $this->setNameTag("§eBoutique");
    }

    public static function getNetworkTypeId(): string
    {
        return "olympia:shop_box";
    }

    public function getName(): string
    {
        return "BoutiqueBox";
    }

    public function getKey(): string
    {
        return BoxsManager::getInstance()->getKeyName(BoxsManager::BOX_SHOP);
    }
}