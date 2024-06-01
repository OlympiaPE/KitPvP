<?php

namespace Olympia\Kitpvp\commands\money;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\command\CommandSender;

class MymoneyCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("mymoney", "Mymoney command", "/mymoney");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof OlympiaPlayer) {
            $sender->sendMessage(str_replace(
                "{money}",
                $sender->getMoney(),
                ConfigManager::getInstance()->getNested("messages.my-money")
            ));
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}