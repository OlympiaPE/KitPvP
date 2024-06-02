<?php

namespace Olympia\Kitpvp\listeners\block;

use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\constants\Permissions;
use pocketmine\event\block\BlockPlaceEvent as Event;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;

class BlockPlaceEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onPlace(Event $event): void
    {
        $player = $event->getPlayer();
        if (!$player->hasPermission(Permissions::BLOCK_PLACE)) {
            $player->sendMessage(Managers::CONFIG()->getNested("messages.not-allowed"));
            $event->cancel();
        }
    }
}