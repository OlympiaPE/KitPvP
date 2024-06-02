<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\entities\SessionCooldowns;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemConsumeEvent as Event;
use pocketmine\item\ItemTypeIds;

class PlayerItemConsumeEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onItemConsume(Event $event): void
    {
        /** @var Session $player */
        $player = $event->getPlayer();
        $id = $event->getItem()->getTypeId();

        if($id === ItemTypeIds::ENCHANTED_GOLDEN_APPLE) {

            if(!$player->getCooldowns()->hasCooldown(SessionCooldowns::COOLDOWN_NOTCH)) {
                $duration = (int)Managers::CONFIG()->getNested("cooldowns.notch.duration");
                $player->getCooldowns()->setCooldown(SessionCooldowns::COOLDOWN_NOTCH, $duration);
            }else{
                $event->cancel();
                $duration = $player->getCooldowns()->getCooldown(SessionCooldowns::COOLDOWN_NOTCH);
                $player->sendMessage(str_replace(
                    "{time}",
                    $duration,
                    Managers::CONFIG()->getNested("cooldowns.notch.message")
                ));
            }
        }
    }
}