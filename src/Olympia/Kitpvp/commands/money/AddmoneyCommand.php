<?php

namespace Olympia\Kitpvp\commands\money;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\MoneyManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class AddmoneyCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_ADDMONEY;
        parent::__construct("addmoney", "Addmoney command", "/addmoney [player] [money]");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(count($args) > 1 && is_numeric($args[1]) && !str_contains($args[1], '.')) {

            if(
                !is_null(Server::getInstance()->getOfflinePlayerData($args[0])) ||
                MoneyManager::getInstance()->inPlayersMoneyData($args[0])
            ) {

                $money = intval($args[1]);

                if(!is_null($player = Server::getInstance()->getPlayerExact($args[0]))) {
                    /** @var OlympiaPlayer $player */
                    $player->addMoney($money);
                    $player->sendMessage(str_replace(
                        ["{player}", "{money}"],
                        [$sender->getName(), (string)$money],
                        ConfigManager::getInstance()->getNested("messages.receive-money")
                    ));
                }else{
                    MoneyManager::getInstance()->addOfflinePlayerMoney($args[0], $money);
                }

                MoneyManager::getInstance()->updatePlayerMoneyData($args[0]);
                $sender->sendMessage(str_replace(
                    ["{player}", "{money}"],
                    [$args[0], (string)$money],
                    ConfigManager::getInstance()->getNested("messages.add-money")
                ));
            }else{
                $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.player-not-found"));
            }
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}