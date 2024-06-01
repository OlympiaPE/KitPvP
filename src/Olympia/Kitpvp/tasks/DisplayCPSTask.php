<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\managers\types\CPSManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

final class DisplayCPSTask extends Task
{
    public function onRun(): void
    {
        /** @var OlympiaPlayer $player */
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if($player->getSettings()['cps']) {
                $cps = CPSManager::getInstance()->get($player->getName());
                $player->sendTip("§6CPS : §7$cps");
            }
        }
    }
}