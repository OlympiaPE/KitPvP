<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\handlers\Handlers;
use pocketmine\scheduler\Task;

final class ChestsRefillTask extends Task
{
    private int $secondLevelProgression = 0;

    public function onRun(): void
    {
        // LEvEL 1
        Handlers::CHEST_REFILL()->refillChests(1);

        // LEVEL 2
        $this->secondLevelProgression++;
        if ($this->secondLevelProgression >= 6) {
            $this->secondLevelProgression = 0;
            Handlers::CHEST_REFILL()->refillChests(2);
        }
    }
}