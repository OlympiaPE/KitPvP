<?php

namespace Olympia\Kitpvp\listeners\player;

use JsonException;
use Olympia\Kitpvp\managers\types\CosmeticsManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChangeSkinEvent as Event;

class PlayerChangeSkinEvent implements Listener
{
    /**
     * @throws JsonException
     */
    public function onChangeSkin(Event $event): void
    {
        /** @var OlympiaPlayer $player */
        $player = $event->getPlayer();

        CosmeticsManager::getInstance()->savePlayerSkin($player->getName(), $event->getNewSkin());
        $player->setSkin($event->getNewSkin());

        $equippedCosmetics = $player->getAllEquippedCosmetics();
        foreach ($equippedCosmetics as $cosmeticType => $cosmeticInfos) {
            if ($cosmeticInfos) {
                CosmeticsManager::getInstance()->applyPlayerCosmetic($player, $cosmeticInfos["category"], $cosmeticInfos["cosmetic"], $cosmeticType);
            }
        }

        $event->setNewSkin($player->getSkin());
    }
}