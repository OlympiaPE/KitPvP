<?php

namespace Olympia\Kitpvp\listeners\player;

use JsonException;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChangeSkinEvent as Event;

class PlayerChangeSkinEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    /**
     * @throws JsonException
     */
    public function onChangeSkin(Event $event): void
    {
        /** @var Session $player */
        $player = $event->getPlayer();

        Managers::COSMETICS()->savePlayerSkin($player->getName(), $event->getNewSkin());
        $player->setSkin($event->getNewSkin());

        $equippedCosmetics = $player->getAllEquippedCosmetics();
        foreach ($equippedCosmetics as $cosmeticType => $cosmeticInfos) {
            if ($cosmeticInfos) {
                Managers::COSMETICS()->applyPlayerCosmetic($player, $cosmeticInfos["category"], $cosmeticInfos["cosmetic"], $cosmeticType);
            }
        }

        $event->setNewSkin($player->getSkin());
    }
}