<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\handlers\Handlers;
use pocketmine\scheduler\Task;
use pocketmine\Server;

final class DisplayCPSTask extends Task
{
    public function onRun(): void
    {
        /** @var Session $player */
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if($player->getSettings()['cps']) {
                $cps = Handlers::CPS()->get($player->getName());
                $player->sendTip("§6CPS : §7$cps");
            }
        }
    }
}