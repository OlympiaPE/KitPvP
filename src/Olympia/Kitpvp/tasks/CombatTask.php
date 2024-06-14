<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\entities\Session;
use pocketmine\scheduler\Task;
use pocketmine\Server;

final class CombatTask extends Task
{
    public function onRun(): void
    {
        foreach (Managers::COMBAT()->getAllPlayersInFight() as $player => $time) {
            /** @var Session $player */
            if(!is_null($player = Server::getInstance()->getPlayerExact($player))) {
                if(!Managers::COMBAT()->inFight($player)) {
                    Managers::COMBAT()->removePlayerFight($player);
                    $player->sendMessage(Managers::CONFIG()->getNested("messages.no-longer-in-combat"));
                }
            }
        }
    }
}