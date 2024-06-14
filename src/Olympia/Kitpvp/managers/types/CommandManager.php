<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Manager;
use Olympia\Kitpvp\utils\FileUtil;
use pocketmine\command\Command;
use Symfony\Component\Filesystem\Path;

class CommandManager extends Manager
{
    /**
     * @return void
     */
    public function onLoad(): void
    {
        $commandMap = Loader::getInstance()->getServer()->getCommandMap();

        $toUnregister = [
            'ban',
            'unban',
            'kick',
            'me',
            'say',
            'list',
            'stop',
        ];

        foreach ($toUnregister as $command) {
            $cmd = $commandMap->getCommand($command);
            if($cmd) {
                $commandMap->unregister($cmd);
            }
        }

        FileUtil::callDirectory(Path::join("commands"), function(string $name) use ($commandMap): void {
            $command = new $name();
            if ($command instanceof Command) {
                $commandMap->register($command->getName(), $command);
            }
        }, null, ["Olympia\Kitpvp\commands\OlympiaCommand"]);
    }
}