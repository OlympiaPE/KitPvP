<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent as Event;

class PlayerCreationEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onCreation(Event $event): void
    {
        $event->setPlayerClass(Session::class);
    }
}