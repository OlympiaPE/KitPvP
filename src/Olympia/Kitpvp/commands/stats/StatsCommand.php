<?php

namespace Olympia\Kitpvp\commands\stats;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\menu\forms\StatsForm;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\command\CommandSender;

class StatsCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("stats", "Stats command", "/stats", ['cos']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof OlympiaPlayer) {
            if(isset($args[0])) {
                $target = $args[0];
                if(!is_null($sender->getServer()->getPlayerExact($target))) {
                    StatsForm::sendBaseMenu($sender, $target, true);
                }elseif(!is_null($sender->getServer()->getOfflinePlayerData($target))) {
                    StatsForm::sendBaseMenu($sender, $target, false);
                }else{
                    $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.player-not-found"));
                }
            }else{
                StatsForm::sendBaseMenu($sender, $sender->getName(), true);
            }
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}