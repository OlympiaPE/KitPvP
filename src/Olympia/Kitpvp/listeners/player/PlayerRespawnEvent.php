<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent as Event;
use pocketmine\Server;
use pocketmine\world\Position;

class PlayerRespawnEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onRespawn(Event $event): void
    {
        $spawnInfos = Managers::CONFIG()->get("spawn");
        $x = (int)$spawnInfos["x"];
        $y = (int)$spawnInfos["y"];
        $z = (int)$spawnInfos["z"];
        $spawnWorld = Server::getInstance()->getWorldManager()->getWorldByName($spawnInfos["world"]);
        $position = new Position($x, $y, $z, $spawnWorld);
        $event->setRespawnPosition($position);
    }
}