<?php

namespace Olympia\Kitpvp\commands\money;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\MoneyManager;
use pocketmine\command\CommandSender;

class TopmoneyCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("topmoney", "Topmoney command", "/topmoney");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $messages = ConfigManager::getInstance()->getNested("leaderboards.money");
        $moneyData = MoneyManager::getInstance()->getPlayersMoneyData();
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