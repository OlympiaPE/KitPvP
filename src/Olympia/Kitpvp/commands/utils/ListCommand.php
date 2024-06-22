<?php

namespace Olympia\Kitpvp\commands\utils;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class ListCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("list", "List command", "/list");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $onlinePlayers = $sender->getServer()->getOnlinePlayers();
        $playersCount = count($onlinePlayers);

        if ($playersCount > 0) {
            $playerNames = array_map(function(Player $player) : string{
                return $player->getName();
            }, array_filter($onlinePlayers, function(Player $player) use ($sender) : bool{
                return !($sender instanceof Player) || $sender->canSee($player);
            }));
            sort($playerNames, SORT_STRING);

            $sender->sendMessage(str_replace(
                ["{playersCount}", "{playersNames}"],
                [$playersCount, implode(", ", $playerNames)],
                Managers::CONFIG()->getNested("messages.list")
            ));
        }else{
            $sender->sendMessage(str_replace(
                ["{playersCount}", "{playersNames}"],
                [$playersCount, ""],
                Managers::CONFIG()->getNested("messages.list")
            ));
        }
    }
}