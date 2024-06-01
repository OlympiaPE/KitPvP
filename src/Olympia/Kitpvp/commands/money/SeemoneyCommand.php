<?php

namespace Olympia\Kitpvp\commands\money;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\MoneyManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class SeemoneyCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("seemoney", "Seemoney command", "/seemoney [player]");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(isset($args[0])) {

            $playerName = $args[0];
            /** @var ?OlympiaPlayer $player */
            $player = Server::getInstance()->getPlayerExact($playerName);

            if(is_null($player)) {
                if(MoneyManager::getInstance()->hasOfflinePlayerMoneyData($playerName)) {
                    $money = MoneyManager::getInstance()->getOfflinePlayerMoney($playerName);
                }else{
                    $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.player-not-found"));
                    return;
                }
            }else{
                $money = $player->getMoney();
            }

            $sender->sendMessage(str_replace(
                ["{player}", "{money}"],
                [$playerName, (string)$money],
                ConfigManager::getInstance()->getNested("messages.see-money")
            ));
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}