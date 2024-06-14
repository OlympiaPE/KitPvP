<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent as Event;
use pocketmine\Server;

class PlayerPreLoginEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onPreLogin(Event $event): void
    {
        $playerInfo = $event->getPlayerInfo();
        $uuid = $playerInfo->getUuid()->toString();

        if (!Managers::DATABASE()->hasUuidData($uuid)) {
            Managers::DATABASE()->createUuidData($uuid, $playerInfo->getUsername());
        }

        if(
            Server::getInstance()->getNameBans()->isBanned($playerInfo->getUsername()) ||
            Server::getInstance()->getIPBans()->isBanned($playerInfo->getUsername())
        ){
            $entry = Server::getInstance()->getNameBans()->getEntry($playerInfo->getUsername());
            $dateTime = $entry->getExpires();
            $serializedDate = $dateTime->format('d/m/Y H:i');
            $staff = $entry->getSource();
            $reason = $entry->getReason();
            $event->setKickFlag(Event::KICK_FLAG_BANNED, str_replace(
                ["{staff}", "{reason}", "{date}"],
                [$staff, $reason, $serializedDate],
                Managers::CONFIG()->getNested("messages.ban-screen")
            ));
        }
    }
}