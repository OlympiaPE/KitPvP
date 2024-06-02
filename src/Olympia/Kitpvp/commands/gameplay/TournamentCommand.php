<?php

namespace Olympia\Kitpvp\commands\gameplay;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\menu\forms\TournamentForm;
use pocketmine\command\CommandSender;

class TournamentCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("tournament", "Tournament command", "/tournament", ['event', 'tournois']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof Session) {
            if (isset($args[0]) && $args[0] === "join") {
                if (Managers::TOURNAMENT()->hasCurrentTournament()) {
                    if (Managers::TOURNAMENT()->isTournamentStarted()) {
                        $sender->sendMessage(Managers::CONFIG()->getNested("messages.tournament-join-error"));
                    }else{
                        if (
                            empty($sender->getInventory()->getContents()) &&
                            empty($sender->getArmorInventory()->getContents()) &&
                            empty($sender->getOffHandInventory()->getContents())
                        ) {
                            Managers::TOURNAMENT()->getTournament()->addPlayer($sender);
                        }else{
                            $sender->sendMessage(Managers::CONFIG()->getNested("messages.inventory-must-be-empty"));
                        }
                    }
                }else{
                    $sender->sendMessage(Managers::CONFIG()->getNested("messages.tournament-join-error"));
                }
            }else{
                TournamentForm::sendBaseMenu($sender);
            }
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}