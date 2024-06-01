<?php

namespace Olympia\Kitpvp\listeners\inventory;

use Olympia\Kitpvp\managers\types\TournamentManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\event\Listener;
use pocketmine\event\inventory\InventoryTransactionEvent as Event;

class InventoryTransactionEvent implements Listener
{
    public function onTransaction(Event $event): void
    {
        /** @var OlympiaPlayer $source */
        $source = $event->getTransaction()->getSource();

        if ($source->inTournament()) {
            if (!TournamentManager::getInstance()->isTournamentStarted()) {
                $event->cancel();
            }
        }
    }
}