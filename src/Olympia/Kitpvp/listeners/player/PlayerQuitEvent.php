<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\duel\DuelStates;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent as Event;
use pocketmine\lang\Language;

class PlayerQuitEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onQuit(Event $event): void
    {
        /** @var Session $player */
        $player = $event->getPlayer();
        $playerName = $player->getName();
        $isServerRunning = Loader::getInstance()->isRunning();

        $event->setQuitMessage(str_replace("{player}", $playerName, Managers::CONFIG()->getNested("messages.quit")));

        $player->updatePlayingTime();

        if ($player->getDuelState() === Session::DUEL_STATE_FIGHTER) {
            foreach(Managers::DUEL()->getPlayerDuels($player) as $duel) {
                if ($duel->getState() === DuelStates::STARTING || $duel->getState() === DuelStates::IN_PROGRESS) {
                    $duel->setWinner(array_values(array_diff($duel->getPlayersName(), [$playerName]))[0]);
                }
                $duel->end(!$isServerRunning);
            }
        }

        if ($player->inTournament()) {
            $tournament = Managers::TOURNAMENT()->getTournament();
            if ($tournament->isDamageable($player)) {
                $fighters = $tournament->getFightersNames();
                $winnerName = $fighters[0] === $playerName ? $fighters[1] : $fighters[0];
                $tournament->setFightWinner($winnerName);
            }
            $tournament->removePlayer($player, true);
        }

        if (!$isServerRunning) {
            return;
        }

        if(Managers::COMBAT()->inFight($player)) {
            $player->kill();
            Managers::COMBAT()->removePlayerFight($player);
        }

        if($player->getSettings()["scoreboard"]) {
            Managers::SCOREBOARD()->removePlayerToDisplay($player);
        }

        if(Managers::MODERATION()->isFreeze($player)) {
            Managers::MODERATION()->removeFreeze($player);
            $sender = new ConsoleCommandSender($player->getServer(), new Language("fra"));
            $command = "ban {$player->getName()} 30d Déconnexion freeze";
            $player->getServer()->dispatchCommand($sender, $command);
        }
    }
}