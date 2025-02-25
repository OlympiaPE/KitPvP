<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\entities\SessionCooldowns;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\scheduler\Task;
use pocketmine\Server;

final class ScoreboardTask extends Task
{
    public function onRun(): void
    {
        $server = Server::getInstance();
        foreach(Managers::SCOREBOARD()->getPlayersToDisplay() as $playerName) {

            /** @var Session $player */
            $player = $server->getPlayerExact($playerName);

            if (is_null($player)) continue;

            $combatTime = Managers::COMBAT()->getPlayerFightTimeRemaining($player);
            $cooldownPearl = $player->getCooldowns()->getCooldown(SessionCooldowns::COOLDOWN_ENDERPEARL);
            $cooldownNotch = $player->getCooldowns()->getCooldown(SessionCooldowns::COOLDOWN_NOTCH);

            $kills = $player->getKill();
            $deaths = $player->getDeath();
            $killstreak = $player->getKillstreak();
            $kdr = $deaths > 0 ? round($kills / $deaths, 2) : 0;
            $money = $player->getMoney() . "$";

            $sm = Managers::SCOREBOARD();
            $sm->new($player, "ObjectiveName",  "§l§6Olympia §f/ KITPVP");
            $sm->setLine($player, 1, "§r----------------");
            $sm->setLine($player, 2, "§l§6Cooldowns");
            $sm->setLine($player, 3, " §f* §cCombat: §f$combatTime");
            $sm->setLine($player, 4, " §f* §3Pearl: §f$cooldownPearl");
            $sm->setLine($player, 5, " §f* §3Notch: §f$cooldownNotch");
            $sm->setLine($player, 6, "§r        ");
            $sm->setLine($player, 7, "§l§6Vous");
            $sm->setLine($player, 8, " §6* §fKills: $kills");
            $sm->setLine($player, 9, " §6* §fDeaths: $deaths");
            $sm->setLine($player, 10, " §6* §fKillstreak: $killstreak");
            $sm->setLine($player, 11, " §6* §fKDR: $kdr");
            $sm->setLine($player, 12, " §6* §fMoney: $money");
        }
    }
}