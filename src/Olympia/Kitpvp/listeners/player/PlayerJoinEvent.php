<?php

namespace Olympia\Kitpvp\listeners\player;

use JsonException;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent as Event;
use pocketmine\player\GameMode;
use pocketmine\world\Position;

class PlayerJoinEvent implements Listener
{
    /**
     * @throws JsonException
     */
    #[EventAttribute(EventPriority::NORMAL)]
    public function onJoin(Event $event): void
    {
        /** @var Session $player */
        $player = $event->getPlayer();
        $playerName = $player->getName();

        $player->setConnectionTime();
        $player->setHealth($player->getMaxHealth());

        if (!$player->getServer()->isOp($playerName)) {

            $player->setGamemode(GameMode::ADVENTURE);

            $spawnInfos = Managers::CONFIG()->get("spawn");
            $x = (int)$spawnInfos["x"];
            $y = (int)$spawnInfos["y"];
            $z = (int)$spawnInfos["z"];
            $spawnWorld = $player->getServer()->getWorldManager()->getWorldByName($spawnInfos["world"]);
            $position = new Position($x, $y, $z, $spawnWorld);
            $player->teleport($position);
        }

        if($player->getSettings()["scoreboard"]) {
            Managers::SCOREBOARD()->addPlayerToDisplay($player);
        }

        Managers::COSMETICS()->savePlayerSkin($playerName, $player->getSkin());
        Managers::COSMETICS()->updatePlayerCosmeticsInfos($player);

        foreach ($player->getAllEquippedCosmetics() as $cosmeticType => $cosmeticInfos) {
            if ($cosmeticInfos) {
                Managers::COSMETICS()->applyPlayerCosmetic($player, $cosmeticInfos["category"], $cosmeticInfos["cosmetic"], $cosmeticType);
            }
        }

        if($player->hasPlayedBefore()) {
            $event->setJoinMessage(str_replace("{player}", $playerName, Managers::CONFIG()->getNested("messages.join")));
        }else{
            $player->sendMessage(str_replace("{player}", $playerName, Managers::CONFIG()->getNested("messages.first-join-private")));
            $event->setJoinMessage(str_replace("{player}", $playerName, Managers::CONFIG()->getNested("messages.first-join-general")));
        }
    }
}