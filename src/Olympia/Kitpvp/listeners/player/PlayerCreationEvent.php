<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent as Event;

class PlayerCreationEvent implements Listener
{
    public function onCreation(Event $event): void
    {
        $event->setPlayerClass(OlympiaPlayer::class);
    }
}