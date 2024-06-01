<?php

namespace Olympia\Kitpvp\listeners\block;

use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent as Event;

class BlockBreakEvent implements Listener
{
    public function onBreak(Event $event): void
    {
        $player = $event->getPlayer();
        if (!$player->hasPermission(Permissions::BLOCK_BREAK)) {
            $player->sendMessage(ConfigManager::getInstance()->getNested("messages.not-allowed"));
            $event->cancel();
        }
    }
}