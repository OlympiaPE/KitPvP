<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent as Event;

class PlayerExhaustEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onExhaust(Event $event): void
    {
        $event->cancel();
    }
}