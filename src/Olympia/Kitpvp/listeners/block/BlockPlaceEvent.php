<?php

namespace Olympia\Kitpvp\listeners\block;

use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockPlaceEvent as Event;

class BlockPlaceEvent implements Listener
{
    public function onPlace(Event $event): void
    {
        $player = $event->getPlayer();
        if (!$player->hasPermission(Permissions::BLOCK_PLACE)) {
            $player->sendMessage(ConfigManager::getInstance()->getNested("messages.not-allowed"));
            $event->cancel();
        }
    }
}