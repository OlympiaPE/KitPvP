<?php

namespace Olympia\Kitpvp\commands\gameplay;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\menu\forms\AreneForm;
use pocketmine\command\CommandSender;

class AreneCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("arene", "Arene command", "/arene");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof Session) {
            AreneForm::sendBaseMenu($sender);
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}