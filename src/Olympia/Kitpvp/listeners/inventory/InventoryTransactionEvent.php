<?php

namespace Olympia\Kitpvp\listeners\inventory;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\event\EventPriority;
use pocketmine\event\inventory\InventoryTransactionEvent as Event;
use pocketmine\event\Listener;

class InventoryTransactionEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onTransaction(Event $event): void
    {
        /** @var Session $source */
        $source = $event->getTransaction()->getSource();

        if ($source->inTournament()) {
            if (!Managers::TOURNAMENT()->isTournamentStarted()) {
                $event->cancel();
            }
        }
    }
}