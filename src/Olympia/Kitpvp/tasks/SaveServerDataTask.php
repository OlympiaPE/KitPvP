<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\managers\Managers;
use pocketmine\scheduler\Task;

final class SaveServerDataTask extends Task
{
    public function onRun(): void
    {
        Managers::DATABASE()->saveServerData();
    }
}