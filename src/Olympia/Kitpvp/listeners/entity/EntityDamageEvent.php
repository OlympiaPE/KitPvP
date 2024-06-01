<?php

namespace Olympia\Kitpvp\listeners\entity;

use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\DuelManager;
use Olympia\Kitpvp\managers\types\TournamentManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent as Event;

class EntityDamageEvent implements Listener
{
    public function onDamage(Event $event): void
    {
        $entity = $event->getEntity();
        $cause = $event->getCause();

        if ($entity instanceof OlympiaPlayer) {

            switch ($cause) {

                case Event::CAUSE_DROWNING:
                case Event::CAUSE_SUFFOCATION:

                    $event->cancel();
                    break;

                case Event::CAUSE_FALL:

                    if ($entity->inTournament()) {

                        $tournament = TournamentManager::getInstance()->getTournament();
                        if ($tournament->getType() === TournamentManager::TOURNAMENT_TYPE_SUMO && $tournament->isDamageable($entity)) {

                            $fighters = $tournament->getFightersNames();
                            $winnerName = $fighters[0] === $entity->getName() ? $fighters[1] : $fighters[0];
                            $tournament->setFightWinner($winnerName);
                            $event->cancel();
                            return;
                        }
                    }

                    if ($entity->getDuelState() === OlympiaPlayer::DUEL_STATE_FIGHTER) {

                        $duel = DuelManager::getInstance()->getDuelById($entity->getDuelId());
                        if ($duel->getType() === DuelManager::DUEL_TYPE_SUMO) {

                            $players = $duel->getPlayersName();
                            $winnerName = $players[0] === $entity->getName() ? $players[1] : $players[0];
                            $duel->setWinner($winnerName);
                            $duel->end();
                            $event->cancel();
                            return;
                        }
                    }

                    $zones = ConfigManager::getInstance()->get("no-fall-damage-zones");
                    $toCheck = $entity->getPosition();

                    foreach ($zones as $zoneInfos) {
                        if(
                            $zoneInfos["min-x"] <= floor($toCheck->getX()) &&
                            $zoneInfos["max-x"] >= floor($toCheck->getX()) &&
                            $zoneInfos["min-z"] <= floor($toCheck->getZ()) &&
                            $zoneInfos["max-z"] >= floor($toCheck->getZ()) &&
                            $zoneInfos["min-y"] <= floor($toCheck->getY()) &&
                            $zoneInfos["max-y"] >= floor($toCheck->getY()) &&
                            $zoneInfos["world"] === $toCheck->getWorld()->getFolderName()
                        ) {
                            $event->cancel();
                            break;
                        }
                    }
                break;
            }

            if (!$event->isCancelled()) {

                if ($entity->getDuelState() === OlympiaPlayer::DUEL_STATE_SPECTATOR) {
                    $event->cancel();
                }

                if ($entity->inTournament() && !TournamentManager::getInstance()->getTournament()->isDamageable($entity)) {
                    $event->cancel();
                }
            }
        }
    }
}