<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\managers\types\StatsManager;
use pocketmine\scheduler\Task;

final class UpdatePlayersStats extends Task
{
    public function onRun(): void
    {
        StatsManager::getInstance()->updateDataCache();
        StatsManager::getInstance()->updateLeaderboard();
    }
}