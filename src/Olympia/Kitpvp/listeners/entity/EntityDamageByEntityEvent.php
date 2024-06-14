<?php

namespace Olympia\Kitpvp\listeners\entity;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\event\entity\EntityDamageByEntityEvent as Event;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;

class EntityDamageByEntityEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onDamage(Event $event): void
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();

        $event->setKnockBack(Managers::COMBAT()->getKb());
        $event->setVerticalKnockBackLimit(Managers::COMBAT()->getVerticalKbLimit());
        $event->setAttackCooldown(Managers::COMBAT()->getAttackCooldown());

        if ($entity instanceof Session && $damager instanceof Session) {

            if ($damager->getName() === $entity->getName()) {
                return;
            }

            if(!$entity->canFight() || !$damager->canFight()) {
                $event->cancel();
                return;
            }

            if ($damager->getDuelState() !== $entity->getDuelState()) {
                $event->cancel();
                return;
            }

            if (
                $damager->getDuelState() === Session::DUEL_STATE_FIGHTER &&
                $entity->getDuelState() === Session::DUEL_STATE_FIGHTER
            ) {
                $duel = Managers::DUEL()->getDuelById($damager->getDuelId());
                $kbInfos = $duel->getKbInfos();
                $event->setKnockBack($kbInfos["kb"]);
                $event->setVerticalKnockBackLimit($kbInfos["vertical-kb-limit"]);
                $event->setAttackCooldown($kbInfos["attackcooldown"]);
                return;
            }

            if ($damager->inTournament() !== $entity->inTournament()) {
                $event->cancel();
                return;
            }

            if ($damager->inTournament()) {
                $tournament = Managers::TOURNAMENT()->getTournament();
                if ($tournament->isDamageable($damager) && $tournament->isDamageable($entity)) {
                    $kbInfos = $tournament->getKbInfos();
                    $event->setKnockBack($kbInfos["kb"]);
                    $event->setVerticalKnockBackLimit($kbInfos["vertical-kb-limit"]);
                    $event->setAttackCooldown($kbInfos["attackcooldown"]);
                }else{
                    $event->cancel();
                }
                return;
            }

            if(!Managers::COMBAT()->inFight($damager)) {
                $damager->sendMessage(Managers::CONFIG()->getNested("messages.enter-in-combat"));
            }
            if(!Managers::COMBAT()->inFight($entity)) {
                $entity->sendMessage(Managers::CONFIG()->getNested("messages.enter-in-combat"));
            }

            Managers::COMBAT()->updatePlayerFight($entity);
            Managers::COMBAT()->updatePlayerFight($damager);
        }
    }
}