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
        FileUtil::callDirectory(Path::join("commands"), function(string $name): void {
            $command = new $name();
            if ($command instanceof Command) {
                Loader::getInstance()->getServer()->getCommandMap()->register($command->getName(), $command);
            }
        }, null, ["Olympia\Kitpvp\commands\OlympiaCommand"]);
    }
}