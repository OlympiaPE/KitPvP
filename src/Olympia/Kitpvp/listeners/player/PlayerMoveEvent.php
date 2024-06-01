<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent as Event;

class PlayerMoveEvent implements Listener
{
    public function onJoin(Event $event): void
    {
        /** @var OlympiaPlayer $player */
        $player = $event->getPlayer();
        $blockPosition = $event->getTo()->subtract(0, 1, 0)->floor();
        $block = $player->getWorld()->getBlock($blockPosition);

        if($block->getTypeId() === BlockTypeIds::REDSTONE) {

            $lengthPower = ConfigManager::getInstance()->getNested("redstone-bump.length-power");
            $heightPower = ConfigManager::getInstance()->getNested("redstone-bump.height-power");

            $motion = clone $player->getMotion();
            $motion->x += $player->getDirectionVector()->getX() * $lengthPower;
            $motion->y += $heightPower;
            $motion->z += $player->getDirectionVector()->getZ() * $lengthPower;

            $player->setMotion($motion);
        }
    }
}