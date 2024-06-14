<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\managers\Managers;
use pocketmine\scheduler\Task;

final class UpdatePlayersStats extends Task
{
    public function onRun(): void
    {
        Managers::STATS()->updateDataCache();
        Managers::STATS()->updateLeaderboard();
    }
}