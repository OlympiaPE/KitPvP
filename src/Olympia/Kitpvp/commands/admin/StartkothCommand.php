<?php

namespace Olympia\Kitpvp\commands\admin;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\EventsManager;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;

class StartkothCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_STARTKOTH;
        parent::__construct("startkoth", "Startkoth command", "/startkoth");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!EventsManager::getInstance()->hasCurrentKoth()) {
            $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.koth-player-start"));
            EventsManager::getInstance()->createKoth();
        }else{
            $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.koth-already-started"));
        }
    }
}