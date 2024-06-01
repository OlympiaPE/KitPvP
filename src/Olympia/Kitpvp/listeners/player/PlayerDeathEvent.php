<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\managers\types\CombatManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\DuelManager;
use Olympia\Kitpvp\managers\types\TournamentManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent as Event;

class PlayerDeathEvent implements Listener
{
    public function onDeath(Event $event): void
    {
        /** @var OlympiaPlayer $player */
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();

        $event->setDeathMessage("");

        $player->addDeath();

        if (CombatManager::getInstance()->inFight($player)) {
            CombatManager::getInstance()->removePlayerFight($player);
        }

        if($cause instanceof EntityDamageByEntityEvent) {

            $killer = $cause->getDamager();
            if ($killer instanceof OlympiaPlayer) {

                if ($killer->getDuelState() === OlympiaPlayer::DUEL_STATE_FIGHTER && $player->getDuelState() === OlympiaPlayer::DUEL_STATE_FIGHTER) {

                    $duel = DuelManager::getInstance()->getDuelById($killer->getDuelId());
                    $duel->setWinner($killer->getName());
                    $duel->end();
                }elseif ($killer->inTournament()) {

                    $tournament = TournamentManager::getInstance()->getTournament();
                    $tournament->setFightWinner($killer->getName());
                }else{

                    $player->resetKillstreak();

                    $killer->addKill();
                    $killer->addMoney(10);
                    $killer->addKillstreak();

                    if ($killer->getKillstreak() > $killer->getBestKillstreak()) {
                        $killer->setBestKillstreak($killer->getKillstreak());
                    }

                    if (CombatManager::getInstance()->inFight($killer)) {
                        CombatManager::getInstance()->removePlayerFight($killer);
                    }

                    $message = str_replace(
                        ["{player}", "{killer}"],
                        [$player->getDisplayName(), $killer->getDisplayName()],
                        ConfigManager::getInstance()->getNested("messages.kill")
                    );

                    $player->getServer()->getLogger()->info($message);

                    /** @var OlympiaPlayer $playerToSendMessage */
                    foreach ($killer->getServer()->getOnlinePlayers() as $playerToSendMessage) {
                        if($playerToSendMessage->getSettings()['kill-message']) {
                            $playerToSendMessage->sendMessage($message);
                        }
                    }
                }
            }
        }
    }
}