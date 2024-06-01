<?php

namespace Olympia\Kitpvp\commands\stats;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\StatsManager;
use pocketmine\command\CommandSender;

class TopkillCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("topkill", "Topkill command", "/topkill");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $messages = ConfigManager::getInstance()->getNested("leaderboards.kill");
        $killLeaderboard = StatsManager::getInstance()->getLeaderboard(StatsManager::STATS_KILL);

        $message = $messages["title"];
        $top = 1;
        foreach ($killLeaderboard as $player => $kill) {
            $message .= "\n" . str_replace(
                ["{top}", "{player}", "{kill}"],
                [$top, $player, $kill],
                $messages["line"]
            );
            $top++;
        }

        $sender->sendMessage($message);
    }
}