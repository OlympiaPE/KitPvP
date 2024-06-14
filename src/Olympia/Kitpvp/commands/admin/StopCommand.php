<?php

namespace Olympia\Kitpvp\commands\admin;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Managers;
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

        Loader::getInstance()->setRunning(false);

        foreach ($server->getOnlinePlayers() as $player) {
            $player->kick(Managers::CONFIG()->getNested("messages.server-restart"));
        }

        $server->getConfigGroup()->setConfigBool(ServerProperties::WHITELIST, true);
        $server->getConfigGroup()->save();

        Loader::getInstance()->getScheduler()->cancelAllTasks();
        //Loader::getInstance()->unloadManagers();

        Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($server, $serverIsWhiteListed) {
            $server->getConfigGroup()->setConfigBool(ServerProperties::WHITELIST, $serverIsWhiteListed);
            $server->getConfigGroup()->save();
            $server->shutdown();
        }), 20);
    }
}