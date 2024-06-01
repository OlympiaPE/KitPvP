<?php

namespace Olympia\Kitpvp\commands\admin;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\Core;
use Olympia\Kitpvp\managers\types\ConfigManager;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\scheduler\ClosureTask;
use pocketmine\ServerProperties;

class StopCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = DefaultPermissionNames::COMMAND_STOP;
        parent::__construct("stop", "Stop command", "/stop");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $server = $sender->getServer();
        $serverIsWhiteListed = $server->getConfigGroup()->getConfigBool(ServerProperties::WHITELIST);

        Core::getInstance()->setRunning(false);

        foreach ($server->getOnlinePlayers() as $player) {
            $player->kick(ConfigManager::getInstance()->getNested("messages.server-restart"));
        }

        $server->getConfigGroup()->setConfigBool(ServerProperties::WHITELIST, true);
        $server->getConfigGroup()->save();

        Core::getInstance()->getScheduler()->cancelAllTasks();
        Core::getInstance()->unloadManagers();

        Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($server, $serverIsWhiteListed) {
            $server->getConfigGroup()->setConfigBool(ServerProperties::WHITELIST, $serverIsWhiteListed);
            $server->getConfigGroup()->save();
            $server->shutdown();
        }), 20);
    }
}