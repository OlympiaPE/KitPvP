<?php

namespace Olympia\Kitpvp\listeners\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent as Event;

class PlayerExhaustEvent implements Listener
{
    public function onExhaust(Event $event): void
    {
        $event->cancel();
    }
}