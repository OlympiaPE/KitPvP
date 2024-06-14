<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Component\EventLoader;
use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Manager;
use Olympia\Kitpvp\utils\FileUtil;
use ReflectionException;
use Symfony\Component\Filesystem\Path;

class ListenerManager extends Manager
{
    /**
     * @return void
     * @throws ReflectionException
     */
    public function onLoad(): void
    {
        FileUtil::callDirectory(Path::join("listeners"), fn(string $name) => EventLoader::loadEventWithClass(Loader::getInstance(), $name));
    }
}