<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent as Event;

class PlayerMoveEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onJoin(Event $event): void
    {
        /** @var Session $player */
        $player = $event->getPlayer();
        $blockPosition = $event->getTo()->subtract(0, 1, 0)->floor();
        $block = $player->getWorld()->getBlock($blockPosition);

        if($block->getTypeId() === BlockTypeIds::REDSTONE) {

            $lengthPower = Managers::CONFIG()->getNested("redstone-bump.length-power");
            $heightPower = Managers::CONFIG()->getNested("redstone-bump.height-power");

            $motion = clone $player->getMotion();
            $motion->x += $player->getDirectionVector()->getX() * $lengthPower;
            $motion->y += $heightPower;
            $motion->z += $player->getDirectionVector()->getZ() * $lengthPower;

            $player->setMotion($motion);
        }
    }
}