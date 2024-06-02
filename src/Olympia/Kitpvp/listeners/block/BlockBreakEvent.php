<?php

namespace Olympia\Kitpvp\listeners\block;

use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\constants\Permissions;
use pocketmine\event\block\BlockBreakEvent as Event;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;

class BlockBreakEvent implements Listener
{

    #[EventAttribute(EventPriority::NORMAL)]
    public function onBreak(Event $event): void
    {
        $player = $event->getPlayer();
        if (!$player->hasPermission(Permissions::BLOCK_BREAK)) {
            $player->sendMessage(Managers::CONFIG()->getNested("messages.not-allowed"));
            $event->cancel();
        }
    }
}