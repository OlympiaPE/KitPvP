<?php

namespace Olympia\Kitpvp\commands\utils;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\menu\gui\StuffGui;
use Olympia\Kitpvp\utils\constants\Permissions;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class StuffCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_STUFF;
        parent::__construct("stuff", "Stuff command", "/stuff [player]");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                if (!is_null($player = $sender->getServer()->getPlayerByPrefix($args[0]))) {
                    $gui = new StuffGui($player);
                    $gui->send($sender);
                }else{
                    $sender->sendMessage(Managers::CONFIG()->getNested("messages.player-not-found"));
                }
            }else{
                $this->sendUsageMessage($sender);
            }
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}