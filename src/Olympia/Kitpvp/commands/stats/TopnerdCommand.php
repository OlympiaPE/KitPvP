<?php

namespace Olympia\Kitpvp\commands\stats;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\StatsManager;
use Olympia\Kitpvp\utils\Utils;
use pocketmine\command\CommandSender;

class TopnerdCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("topnerd", "Topnerd command", "/topnerd");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $messages = Managers::CONFIG()->getNested("leaderboards.nerd");
        $nerdLeaderboard = Managers::STATS()->getLeaderboard(StatsManager::STATS_NERD);

        $message = $messages["title"];
        $top = 1;
        foreach ($nerdLeaderboard as $player => $nerd) {
            $message .= "\n" . str_replace(
                ["{top}", "{player}", "{nerd}"],
                [$top, $player, Utils::durationToShortString($nerd)],
                $messages["line"]
            );
            $top++;
        }

        $sender->sendMessage($message);
    }
}