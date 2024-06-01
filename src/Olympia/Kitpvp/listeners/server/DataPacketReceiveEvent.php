<?php

namespace Olympia\Kitpvp\listeners\server;

use Olympia\Kitpvp\managers\types\CPSManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent as Event;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;

class DataPacketReceiveEvent implements Listener
{
    public function onDataPacketReceive(Event $event): void
    {
        $pk = $event->getPacket();
        $origin = $event->getOrigin();
        /** @var OlympiaPlayer $player */
        $player = $origin->getPlayer();

        switch ($pk->pid()) {

            case PlayerAuthInputPacket::NETWORK_ID:

                /** @var PlayerAuthInputPacket $pk */
                if ($pk->hasFlag(PlayerAuthInputFlags::MISSED_SWING)) {
                    CPSManager::getInstance()->add($player->getName());
                }
                break;

            case InventoryTransactionPacket::NETWORK_ID:

                /** @var InventoryTransactionPacket $pk */
                if ($pk->trData instanceof UseItemOnEntityTransactionData) {
                    CPSManager::getInstance()->add($player->getName());
                }
                break;
        }
    }
}