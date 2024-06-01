<?php

namespace Olympia\Kitpvp\commands\utils;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\menu\forms\SettingsForm;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\command\CommandSender;

class SettingsCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("settings", "Settings command", "/settings");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof OlympiaPlayer) {
            SettingsForm::sendBaseMenu($sender);
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}