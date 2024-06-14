<?php

namespace Olympia\Kitpvp\commands\money;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\command\CommandSender;

class TopmoneyCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("topmoney", "Topmoney command", "/topmoney");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $messages = Managers::CONFIG()->getNested("leaderboards.money");
        $moneyData = Managers::DATABASE()->getPlayersDataByKey("money", true);
        arsort($moneyData);

        $message = $messages["title"];
        $top = 1;
        foreach ($moneyData as $player => $money) {

            if($top > 10) break;

            $message .= "\n" . str_replace(
                ["{top}", "{player}", "{money}"],
                [$top, $player, $money],
                $messages["line"]
            );
            $top++;
        }
        $sender->sendMessage($message);
    }
}