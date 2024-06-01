<?php

namespace Olympia\Kitpvp\commands\navigation;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\menu\forms\ServeurForm;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\command\CommandSender;

class ServeurCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("serveur", "Serveur command", "/serveur");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof OlympiaPlayer) {
            ServeurForm::sendBaseMenu($sender);
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}