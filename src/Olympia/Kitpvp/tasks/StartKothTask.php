<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\handlers\Handlers;
use pocketmine\scheduler\Task;

final class StartKothTask extends Task
{
    public function onRun(): void
    {
        $kothLastCaptureTime = Handlers::KOTH()->getKothLastCaptureTime();
        if(
            !is_null($kothLastCaptureTime) &&
            120 * 60 - (time() - $kothLastCaptureTime) <= 0 &&
            !Handlers::KOTH()->hasCurrentKoth()
        ) {
            Handlers::KOTH()->createKoth();
        }
    }
}