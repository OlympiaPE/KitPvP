<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\managers\types\EventsManager;
use pocketmine\scheduler\Task;

final class ChestsRefillTask extends Task
{
    private int $secondLevelProgression = 0;

    public function onRun(): void
    {
        // LEvEL 1
        EventsManager::getInstance()->refillChests(1);

        // LEVEL 2
        $this->secondLevelProgression++;
        if ($this->secondLevelProgression >= 6) {
            $this->secondLevelProgression = 0;
            EventsManager::getInstance()->refillChests(2);
        }
    }
}