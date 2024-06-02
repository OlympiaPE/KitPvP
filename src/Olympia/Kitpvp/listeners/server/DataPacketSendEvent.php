<?php

namespace Olympia\Kitpvp\listeners\server;

use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent as Event;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\cache\StaticPacketCache;
use pocketmine\network\mcpe\protocol\AvailableActorIdentifiersPacket;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;

class DataPacketSendEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onDataPacketSend(Event $event): void
    {
        foreach($event->getPackets() as $pk) {

            if($pk instanceof AvailableActorIdentifiersPacket) {

                $staticPacketCache = StaticPacketCache::getInstance();

                $defaultActorIdentifierNBT = $staticPacketCache->getAvailableActorIdentifiers()->identifiers->getRoot();
                $tag = $defaultActorIdentifierNBT;
                $idList = $tag->getListTag("idlist");
                foreach(Managers::ENTITIES()->getIdentifierList() as $id) {
                    $idList->push(CompoundTag::create()->setString("id", $id));
                }
                $tag->setTag("idlist", $idList);
                $pk->identifiers = new CacheableNbt($defaultActorIdentifierNBT);
            }
        }
    }
}