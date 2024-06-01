<?php

namespace Olympia\Kitpvp\commands\admin;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\EventsManager;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;

class ChestrefillCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_CHESTREFILL;
        parent::__construct("chestrefill", "Chestrefill command", "/chestrefill [level: 1/2]");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(isset($args[0]) && ($args[0] == 1 || $args[0] == 2)) {

            $sender->sendMessage(str_replace(
                "{level}",
                $args[0],
                ConfigManager::getInstance()->getNested("messages.chestrefill-command-success")
            ));

            EventsManager::getInstance()->refillChests((int)$args[0]);
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}