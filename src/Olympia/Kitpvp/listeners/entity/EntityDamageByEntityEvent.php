<?php

namespace Olympia\Kitpvp\listeners\entity;

use Olympia\Kitpvp\managers\types\CombatManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\DuelManager;
use Olympia\Kitpvp\managers\types\TournamentManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent as Event;

class EntityDamageByEntityEvent implements Listener
{
    public function onDamage(Event $event): void
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();

        $event->setKnockBack(CombatManager::getInstance()->getKb());
        $event->setVerticalKnockBackLimit(CombatManager::getInstance()->getVerticalKbLimit());
        $event->setAttackCooldown(CombatManager::getInstance()->getAttackCooldown());

        if ($entity instanceof OlympiaPlayer && $damager instanceof OlympiaPlayer) {

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
                $damager->getDuelState() === OlympiaPlayer::DUEL_STATE_FIGHTER &&
                $entity->getDuelState() === OlympiaPlayer::DUEL_STATE_FIGHTER
            ) {
                $duel = DuelManager::getInstance()->getDuelById($damager->getDuelId());
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
                $tournament = TournamentManager::getInstance()->getTournament();
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

            if(!CombatManager::getInstance()->inFight($damager)) {
                $damager->sendMessage(ConfigManager::getInstance()->getNested("messages.enter-in-combat"));
            }
            if(!CombatManager::getInstance()->inFight($entity)) {
                $entity->sendMessage(ConfigManager::getInstance()->getNested("messages.enter-in-combat"));
            }

            CombatManager::getInstance()->updatePlayerFight($entity);
            CombatManager::getInstance()->updatePlayerFight($damager);
        }
    }
}