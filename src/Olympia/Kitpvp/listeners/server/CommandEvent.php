<?php

namespace Olympia\Kitpvp\listeners\server;

use Exception;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\utils\constants\Permissions;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\server\CommandEvent as Event;

class CommandEvent implements Listener
{
    /**
     * @throws Exception
     */
    #[EventAttribute(EventPriority::NORMAL)]
    public function onCommand(Event $event): void
    {
        $sender = $event->getSender();

        if ($sender instanceof Session) {

            $command = $event->getCommand();

            switch ($sender->getDuelState()) {

                case Session::DUEL_STATE_NONE:

                    if ($sender->inTournament()) {
                        if (!$sender->hasPermission(Permissions::EXECUTE_COMMANDS_COMBAT)) {
                            $event->cancel();
                        }
                    }else{
                        if (
                            Managers::COMBAT()->inFight($sender) &&
                            !$sender->hasPermission(Permissions::EXECUTE_COMMANDS_COMBAT) &&
                            !in_array($command, Managers::CONFIG()->get("commands-allowed-in-combat"))
                        ) {
                            $sender->sendMessage(Managers::CONFIG()->getNested("messages.command-in-combat"));
                            $event->cancel();
                        }
                    }
                    break;

                case Session::DUEL_STATE_FIGHTER:

                    if (!$sender->hasPermission(Permissions::EXECUTE_COMMANDS_COMBAT)) {

                        $sender->sendMessage(Managers::CONFIG()->getNested("messages.duel-player-execute-command"));
                        $event->cancel();
                    }
                    break;

                case Session::DUEL_STATE_SPECTATOR:

                    if($command === "spawn") {
                        $duel = Managers::DUEL()->getDuelById($sender->getDuelId());
                        $duel->removeSpectator($sender);
                    }elseif(!$sender->hasPermission(Permissions::EXECUTE_COMMANDS_COMBAT)) {
                        $sender->sendMessage(Managers::CONFIG()->getNested("messages.duel-spectator-execute-command"));
                        $event->cancel();
                    }
                    break;
            }

            if(!$event->isCancelled()) {
                Managers::WEBHOOK()->sendMessage("Commande", "**Joueur** : {$sender->getName()}\n**Commande** : $command", WebhookManager::CHANNEL_LOGS_COMMANDS);
            }
        }
    }
}