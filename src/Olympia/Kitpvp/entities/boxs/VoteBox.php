<?php

namespace Olympia\Kitpvp\entities\boxs;

use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\BoxsManager;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;

class VoteBox extends Box
{
    public function __construct(Location $location, CompoundTag $nbt)
    {
        parent::__construct($location, $nbt);
        $this->setNameTag("§aVote");
    }

    public static function getNetworkTypeId(): string
    {
        return "olympia:vote_box";
    }

    public function getName(): string
    {
        return "VoteBox";
    }

    public function getKey(): string
    {
        return Managers::BOXS()->getKeyName(BoxsManager::BOX_VOTE);
    }
}