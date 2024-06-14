<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Manager;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\tasks\BroadcastMessagesTask;
use Olympia\Kitpvp\tasks\ChestsRefillTask;
use Olympia\Kitpvp\tasks\CombatTask;
use Olympia\Kitpvp\tasks\DisplayCPSTask;
use Olympia\Kitpvp\tasks\ExpireHdvItemsTask;
use Olympia\Kitpvp\tasks\SaveServerDataTask;
use Olympia\Kitpvp\tasks\ScoreboardTask;
use Olympia\Kitpvp\tasks\StartKothTask;
use Olympia\Kitpvp\tasks\UpdatePlayersStats;

class TaskManager extends Manager
{
    /**
     * @return void
     */
    public function onLoad(): void
    {
        $scheduler = Loader::getInstance()->getScheduler();
        $periods = Managers::CONFIG()->get("update-periods");

        $scheduler->scheduleRepeatingTask(new ScoreboardTask(), $periods["scoreboard"]);
        $scheduler->scheduleRepeatingTask(new DisplayCPSTask(), $periods["cps"]);
        $scheduler->scheduleRepeatingTask(new CombatTask(), $periods["combat"]);
        $scheduler->scheduleRepeatingTask(new ExpireHdvItemsTask(), $periods["expire-hdv-items"]);
        $scheduler->scheduleRepeatingTask(new StartKothTask(), $periods["start-koth"]);

        $scheduler->scheduleDelayedRepeatingTask(new SaveServerDataTask(), $periods["save-server-data"], $periods["save-server-data"]);
        $scheduler->scheduleDelayedRepeatingTask(new UpdatePlayersStats(), $periods["player-stats"], $periods["player-stats"]);
        $scheduler->scheduleDelayedRepeatingTask(new BroadcastMessagesTask(), $periods["broadcast-message"], $periods["broadcast-message"]);
        $scheduler->scheduleDelayedRepeatingTask(new ChestsRefillTask(), $periods["chest-refill"], $periods["chest-refill"]);
    }
}