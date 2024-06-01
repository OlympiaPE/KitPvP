<?php

namespace Olympia\Kitpvp\commands\gameplay;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\TournamentManager;
use Olympia\Kitpvp\menu\forms\TournamentForm;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\command\CommandSender;

class TournamentCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("tournament", "Tournament command", "/tournament", ['event', 'tournois']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof OlympiaPlayer) {
            if (isset($args[0]) && $args[0] === "join") {
                if (TournamentManager::getInstance()->hasCurrentTournament()) {
                    if (TournamentManager::getInstance()->isTournamentStarted()) {
                        $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.tournament-join-error"));
                    }else{
                        if (
                            empty($sender->getInventory()->getContents()) &&
                            empty($sender->getArmorInventory()->getContents()) &&
                            empty($sender->getOffHandInventory()->getContents())
                        ) {
                            TournamentManager::getInstance()->getTournament()->addPlayer($sender);
                        }else{
                            $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.inventory-must-be-empty"));
                        }
                    }
                }else{
                    $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.tournament-join-error"));
                }
            }else{
                TournamentForm::sendBaseMenu($sender);
            }
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}