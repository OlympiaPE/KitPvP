<?php

namespace Olympia\Kitpvp\listeners\server;

use Exception;
use Olympia\Kitpvp\managers\types\CombatManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\DuelManager;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\event\Listener;
use pocketmine\event\server\CommandEvent as Event;

class CommandEvent implements Listener
{
    /**
     * @throws Exception
     */
    public function onCommand(Event $event): void
    {
        $sender = $event->getSender();

        if ($sender instanceof OlympiaPlayer) {

            $command = $event->getCommand();

            switch ($sender->getDuelState()) {

                case OlympiaPlayer::DUEL_STATE_NONE:

                    if ($sender->inTournament()) {
                        if (!$sender->hasPermission(Permissions::EXECUTE_COMMANDS_COMBAT)) {
                            $event->cancel();
                        }
                    }else{
                        if (
                            CombatManager::getInstance()->inFight($sender) &&
                            !$sender->hasPermission(Permissions::EXECUTE_COMMANDS_COMBAT) &&
                            !in_array($command, ConfigManager::getInstance()->get("commands-allowed-in-combat"))
                        ) {
                            $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.command-in-combat"));
                            $event->cancel();
                        }
                    }
                    break;

                case OlympiaPlayer::DUEL_STATE_FIGHTER:

                    if (!$sender->hasPermission(Permissions::EXECUTE_COMMANDS_COMBAT)) {

                        $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.duel-player-execute-command"));
                        $event->cancel();
                    }
                    break;

                case OlympiaPlayer::DUEL_STATE_SPECTATOR:

                    if($command === "spawn") {
                        $duel = DuelManager::getInstance()->getDuelById($sender->getDuelId());
                        $duel->removeSpectator($sender);
                    }elseif(!$sender->hasPermission(Permissions::EXECUTE_COMMANDS_COMBAT)) {
                        $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.duel-spectator-execute-command"));
                        $event->cancel();
                    }
                    break;
            }

            if(!$event->isCancelled()) {
                WebhookManager::getInstance()->sendMessage("Commande", "**Joueur** : {$sender->getName()}\n**Commande** : $command", WebhookManager::CHANNEL_LOGS_COMMANDS);
            }
        }
    }
}