<?php

namespace Olympia\Kitpvp\commands\admin;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\handlers\Handlers;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\constants\Permissions;
use pocketmine\command\CommandSender;

class ForceClearLagCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_FORCECLEARLAG;
        parent::__construct("forceclearlag", "Forceclearlag command", "/forceclearlag", ['fcl', 'fclearlag']);
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        Handlers::CLEARLAG()->startClearlag(true);
        $sender->sendMessage(Managers::CONFIG()->getNested("messages.clear-lag-force"));
    }
}