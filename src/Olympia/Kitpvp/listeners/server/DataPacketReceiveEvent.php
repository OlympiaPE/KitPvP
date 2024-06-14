<?php

namespace Olympia\Kitpvp\listeners\server;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\handlers\Handlers;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent as Event;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;

class DataPacketReceiveEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onDataPacketReceive(Event $event): void
    {
        $pk = $event->getPacket();
        $origin = $event->getOrigin();
        /** @var Session $player */
        $player = $origin->getPlayer();

        switch ($pk->pid()) {

            case PlayerAuthInputPacket::NETWORK_ID:

                /** @var PlayerAuthInputPacket $pk */
                if ($pk->hasFlag(PlayerAuthInputFlags::MISSED_SWING)) {
                    Handlers::CPS()->add($player->getName());
                }
                break;

            case InventoryTransactionPacket::NETWORK_ID:

                /** @var InventoryTransactionPacket $pk */
                if ($pk->trData instanceof UseItemOnEntityTransactionData) {
                    Handlers::CPS()->add($player->getName());
                }
                break;
        }
    }
}