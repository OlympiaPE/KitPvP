<?php

namespace Olympia\Kitpvp\commands\stats;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\StatsManager;
use pocketmine\command\CommandSender;

class TopdeathCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("topdeath", "Topdeath command", "/topdeath");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $messages = Managers::CONFIG()->getNested("leaderboards.death");
        $deathLeaderboard = Managers::STATS()->getLeaderboard(StatsManager::STATS_DEATH);

        $message = $messages["title"];
        $top = 1;
        foreach ($deathLeaderboard as $player => $death) {
            $message .= "\n" . str_replace(
                ["{top}", "{player}", "{death}"],
                [$top, $player, $death],
                $messages["line"]
            );
            $top++;
        }

        $sender->sendMessage($message);
    }
}