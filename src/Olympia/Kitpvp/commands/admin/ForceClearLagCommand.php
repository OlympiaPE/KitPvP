<?php

namespace Olympia\Kitpvp\commands\admin;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ClearlagManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\utils\Permissions;
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
        ClearlagManager::getInstance()->startClearlag(true);
        $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.clear-lag-force"));
    }
}