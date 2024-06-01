<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\player\PlayerCooldowns;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemConsumeEvent as Event;
use pocketmine\item\ItemTypeIds;

class PlayerItemConsumeEvent implements Listener
{
    public function onItemConsume(Event $event): void
    {
        /** @var OlympiaPlayer $player */
        $player = $event->getPlayer();
        $id = $event->getItem()->getTypeId();

        if($id === ItemTypeIds::ENCHANTED_GOLDEN_APPLE) {

            if(!$player->getCooldowns()->hasCooldown(PlayerCooldowns::COOLDOWN_NOTCH)) {
                $duration = (int)ConfigManager::getInstance()->getNested("cooldowns.notch.duration");
                $player->getCooldowns()->setCooldown(PlayerCooldowns::COOLDOWN_NOTCH, $duration);
            }else{
                $event->cancel();
                $duration = $player->getCooldowns()->getCooldown(PlayerCooldowns::COOLDOWN_NOTCH);
                $player->sendMessage(str_replace(
                    "{time}",
                    $duration,
                    ConfigManager::getInstance()->getNested("cooldowns.notch.message")
                ));
            }
        }
    }
}