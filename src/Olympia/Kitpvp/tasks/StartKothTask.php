<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\managers\types\EventsManager;
use pocketmine\scheduler\Task;

final class StartKothTask extends Task
{
    public function onRun(): void
    {
        $kothLastCaptureTime = EventsManager::getInstance()->getKothLastCaptureTime();
        if(
            !is_null($kothLastCaptureTime) &&
            120 * 60 - (time() - $kothLastCaptureTime) <= 0 &&
            !EventsManager::getInstance()->hasCurrentKoth()
        ) {
            EventsManager::getInstance()->createKoth();
        }
    }
}