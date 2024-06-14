<?php

namespace Olympia\Kitpvp\commands\stats;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\StatsManager;
use pocketmine\command\CommandSender;

class TopkillstreakCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("topkillstreak", "Topkillstreak command", "/topkillstreak");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $messages = Managers::CONFIG()->getNested("leaderboards.killstreak");
        $killstreakLeaderboard = Managers::STATS()->getLeaderboard(StatsManager::STATS_KILLSTREAK);

        $message = $messages["title"];
        $top = 1;
        foreach ($killstreakLeaderboard as $player => $killstreak) {
            $message .= "\n" . str_replace(
                ["{top}", "{player}", "{killstreak}"],
                [$top, $player, $killstreak],
                $messages["line"]
            );
            $top++;
        }

        $sender->sendMessage($message);
    }
}