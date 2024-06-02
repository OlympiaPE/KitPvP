<?php

namespace Olympia\Kitpvp\listeners\entity;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\DuelManager;
use Olympia\Kitpvp\managers\types\TournamentManager;
use pocketmine\event\entity\EntityDamageEvent as Event;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;

class EntityDamageEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onDamage(Event $event): void
    {
        $entity = $event->getEntity();
        $cause = $event->getCause();

        if ($entity instanceof Session) {

            switch ($cause) {

                case Event::CAUSE_DROWNING:
                case Event::CAUSE_SUFFOCATION:

                    $event->cancel();
                    break;

                case Event::CAUSE_FALL:

                    if ($entity->inTournament()) {

                        $tournament = Managers::TOURNAMENT()->getTournament();
                        if ($tournament->getType() === TournamentManager::TOURNAMENT_TYPE_SUMO && $tournament->isDamageable($entity)) {

                            $fighters = $tournament->getFightersNames();
                            $winnerName = $fighters[0] === $entity->getName() ? $fighters[1] : $fighters[0];
                            $tournament->setFightWinner($winnerName);
                            $event->cancel();
                            return;
                        }
                    }

                    if ($entity->getDuelState() === Session::DUEL_STATE_FIGHTER) {

                        $duel = Managers::DUEL()->getDuelById($entity->getDuelId());
                        if ($duel->getType() === DuelManager::DUEL_TYPE_SUMO) {

                            $players = $duel->getPlayersName();
                            $winnerName = $players[0] === $entity->getName() ? $players[1] : $players[0];
                            $duel->setWinner($winnerName);
                            $duel->end();
                            $event->cancel();
                            return;
                        }
                    }

                    $zones = Managers::CONFIG()->get("no-fall-damage-zones");
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

                if ($entity->getDuelState() === Session::DUEL_STATE_SPECTATOR) {
                    $event->cancel();
                }

                if ($entity->inTournament() && !Managers::TOURNAMENT()->getTournament()->isDamageable($entity)) {
                    $event->cancel();
                }
            }
        }
    }
}