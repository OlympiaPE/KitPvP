<?php

namespace Olympia\Kitpvp\commands\utils;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\menu\forms\ReportForm;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\command\CommandSender;

class ReportCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("report", "Report command", "/report");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof OlympiaPlayer) {
            ReportForm::sendBaseMenu($sender);
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}