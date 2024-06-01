<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\managers\types\CombatManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

final class CombatTask extends Task
{
    public function onRun(): void
    {
        foreach (CombatManager::getInstance()->getAllPlayersInFight() as $player => $time) {
            /** @var OlympiaPlayer $player */
            if(!is_null($player = Server::getInstance()->getPlayerExact($player))) {
                if(!CombatManager::getInstance()->inFight($player)) {
                    CombatManager::getInstance()->removePlayerFight($player);
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.no-longer-in-combat"));
                }
            }
        }
    }
}