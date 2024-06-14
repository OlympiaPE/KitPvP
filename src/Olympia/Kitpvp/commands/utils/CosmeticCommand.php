<?php

namespace Olympia\Kitpvp\commands\utils;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\menu\forms\CosmeticForm;
use pocketmine\command\CommandSender;

class CosmeticCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("cosmetic", "Cosmetic command", "/cosmetic", ['cos']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof Session) {
            CosmeticForm::sendBaseMenu($sender);
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}