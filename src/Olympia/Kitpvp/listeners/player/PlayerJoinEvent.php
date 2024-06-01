<?php

namespace Olympia\Kitpvp\listeners\player;

use JsonException;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\CosmeticsManager;
use Olympia\Kitpvp\managers\types\ScoreboardManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent as Event;
use pocketmine\player\GameMode;
use pocketmine\world\Position;

class PlayerJoinEvent implements Listener
{
    /**
     * @throws JsonException
     */
    public function onJoin(Event $event): void
    {
        /** @var OlympiaPlayer $player */
        $player = $event->getPlayer();
        $playerName = $player->getName();

        $player->setConnectionTime();
        $player->setHealth($player->getMaxHealth());

        if (!$player->getServer()->isOp($playerName)) {

            $player->setGamemode(GameMode::ADVENTURE);

            $spawnInfos = ConfigManager::getInstance()->get("spawn");
            $x = (int)$spawnInfos["x"];
            $y = (int)$spawnInfos["y"];
            $z = (int)$spawnInfos["z"];
            $spawnWorld = $player->getServer()->getWorldManager()->getWorldByName($spawnInfos["world"]);
            $position = new Position($x, $y, $z, $spawnWorld);
            $player->teleport($position);
        }

        if($player->getSettings()["scoreboard"]) {
            ScoreboardManager::getInstance()->addPlayerToDisplay($player);
        }

        CosmeticsManager::getInstance()->savePlayerSkin($playerName, $player->getSkin());
        CosmeticsManager::getInstance()->updatePlayerCosmeticsInfos($player);

        foreach ($player->getAllEquippedCosmetics() as $cosmeticType => $cosmeticInfos) {
            if ($cosmeticInfos) {
                CosmeticsManager::getInstance()->applyPlayerCosmetic($player, $cosmeticInfos["category"], $cosmeticInfos["cosmetic"], $cosmeticType);
            }
        }

        if($player->hasPlayedBefore()) {
            $event->setJoinMessage(str_replace("{player}", $playerName, ConfigManager::getInstance()->getNested("messages.join")));
        }else{
            $player->sendMessage(str_replace("{player}", $playerName, ConfigManager::getInstance()->getNested("messages.first-join-private")));
            $event->setJoinMessage(str_replace("{player}", $playerName, ConfigManager::getInstance()->getNested("messages.first-join-general")));
        }
    }
}