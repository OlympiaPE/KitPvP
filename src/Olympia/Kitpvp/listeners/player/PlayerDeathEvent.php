<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent as Event;

class PlayerDeathEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onDeath(Event $event): void
    {
        /** @var Session $player */
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();

        $event->setDeathMessage("");

        $player->addDeath();

        if (Managers::COMBAT()->inFight($player)) {
            Managers::COMBAT()->removePlayerFight($player);
        }

        if($cause instanceof EntityDamageByEntityEvent) {

            $killer = $cause->getDamager();
            if ($killer instanceof Session) {

                if ($killer->getDuelState() === Session::DUEL_STATE_FIGHTER && $player->getDuelState() === Session::DUEL_STATE_FIGHTER) {

                    $duel = Managers::DUEL()->getDuelById($killer->getDuelId());
                    $duel->setWinner($killer->getName());
                    $duel->end();
                }elseif ($killer->inTournament()) {

                    $tournament = Managers::TOURNAMENT()->getTournament();
                    $tournament->setFightWinner($killer->getName());
                }else{

                    $player->resetKillstreak();

                    $killer->addKill();
                    $killer->addMoney(10);
                    $killer->addKillstreak();

                    if ($killer->getKillstreak() > $killer->getBestKillstreak()) {
                        $killer->setBestKillstreak($killer->getKillstreak());
                    }

                    if (Managers::COMBAT()->inFight($killer)) {
                        Managers::COMBAT()->removePlayerFight($killer);
                    }

                    $message = str_replace(
                        ["{player}", "{killer}"],
                        [$player->getDisplayName(), $killer->getDisplayName()],
                        Managers::CONFIG()->getNested("messages.kill")
                    );

                    $player->getServer()->getLogger()->info($message);

                    /** @var Session $playerToSendMessage */
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