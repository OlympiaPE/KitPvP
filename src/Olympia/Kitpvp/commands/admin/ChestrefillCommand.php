<?php

namespace Olympia\Kitpvp\commands\admin;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\handlers\Handlers;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\constants\Permissions;
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
                Managers::CONFIG()->getNested("messages.chestrefill-command-success")
            ));

            Handlers::CHEST_REFILL()->refillChests((int)$args[0]);
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}