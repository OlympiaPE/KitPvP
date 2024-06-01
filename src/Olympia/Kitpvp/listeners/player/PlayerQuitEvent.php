<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\Core;
use Olympia\Kitpvp\duel\DuelStates;
use Olympia\Kitpvp\managers\types\CombatManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\DuelManager;
use Olympia\Kitpvp\managers\types\ModerationManager;
use Olympia\Kitpvp\managers\types\ScoreboardManager;
use Olympia\Kitpvp\managers\types\TournamentManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent as Event;
use pocketmine\lang\Language;

class PlayerQuitEvent implements Listener
{
    public function onQuit(Event $event): void
    {
        /** @var OlympiaPlayer $player */
        $player = $event->getPlayer();
        $playerName = $player->getName();
        $isServerRunning = Core::getInstance()->isRunning();

        $event->setQuitMessage(str_replace("{player}", $playerName, ConfigManager::getInstance()->getNested("messages.quit")));

        $player->updatePlayingTime();

        if ($player->getDuelState() === OlympiaPlayer::DUEL_STATE_FIGHTER) {
            foreach(DuelManager::getInstance()->getPlayerDuels($player) as $duel) {
                if ($duel->getState() === DuelStates::STARTING || $duel->getState() === DuelStates::IN_PROGRESS) {
                    $duel->setWinner(array_values(array_diff($duel->getPlayersName(), [$playerName]))[0]);
                }
                $duel->end(!$isServerRunning);
            }
        }

        if ($player->inTournament()) {
            $tournament = TournamentManager::getInstance()->getTournament();
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

        if(CombatManager::getInstance()->inFight($player)) {
            $player->kill();
            CombatManager::getInstance()->removePlayerFight($player);
        }

        if($player->getSettings()["scoreboard"]) {
            ScoreboardManager::getInstance()->removePlayerToDisplay($player);
        }

        if(ModerationManager::getInstance()->isFreeze($player)) {
            ModerationManager::getInstance()->removeFreeze($player);
            $sender = new ConsoleCommandSender($player->getServer(), new Language("fra"));
            $command = "ban {$player->getName()} 30d Déconnexion freeze";
            $player->getServer()->dispatchCommand($sender, $command);
        }
    }
}