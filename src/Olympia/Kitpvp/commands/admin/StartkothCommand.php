<?php

namespace Olympia\Kitpvp\commands\admin;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\handlers\Handlers;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\constants\Permissions;
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
        if (!Handlers::KOTH()->hasCurrentKoth()) {
            $sender->sendMessage(Managers::CONFIG()->getNested("messages.koth-player-start"));
            Handlers::KOTH()->createKoth();
        }else{
            $sender->sendMessage(Managers::CONFIG()->getNested("messages.koth-already-started"));
        }
    }
}