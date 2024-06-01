<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\managers\types\ConfigManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent as Event;
use pocketmine\Server;
use pocketmine\world\Position;

class PlayerRespawnEvent implements Listener
{
    public function onRespawn(Event $event): void
    {
        $spawnInfos = ConfigManager::getInstance()->get("spawn");
        $x = (int)$spawnInfos["x"];
        $y = (int)$spawnInfos["y"];
        $z = (int)$spawnInfos["z"];
        $spawnWorld = Server::getInstance()->getWorldManager()->getWorldByName($spawnInfos["world"]);
        $position = new Position($x, $y, $z, $spawnWorld);
        $event->setRespawnPosition($position);
    }
}