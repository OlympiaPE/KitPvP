<?php

namespace Olympia\Kitpvp\commands\money;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\MoneyManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class PayCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("pay", "Pay command", "/pay [player] [money]");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof OlympiaPlayer) {

            if(isset($args[0]) && isset($args[1])) {

                if(
                    !is_null(Server::getInstance()->getOfflinePlayerData($args[0])) ||
                    MoneyManager::getInstance()->inPlayersMoneyData($args[0])
                ) {

                    $money = intval($args[1]);

                    if($money > 0 && is_numeric($args[1]) && !str_contains($args[1], '.')) {

                        if($sender->hasEnoughMoney($money)) {

                            $sender->removeMoney($money);
                            MoneyManager::getInstance()->updatePlayerMoneyData($sender->getName());

                            $sender->sendMessage(str_replace(
                                ["{player}", "{money}"],
                                [$args[0], (string)$money],
                                ConfigManager::getInstance()->getNested("messages.pay-money")
                            ));

                            if(!is_null($player = Server::getInstance()->getPlayerExact($args[0]))) {

                                /** @var OlympiaPlayer $player */
                                $player->addMoney($money);
                                $player->sendMessage(str_replace(
                                    ["{player}", "{money}"],
                                    [$sender->getDisplayName(), (string)$money],
                                    ConfigManager::getInstance()->getNested("messages.receive-money")
                                ));
                            }else{
                                MoneyManager::getInstance()->addOfflinePlayerMoney($args[0], $money);
                            }
                            MoneyManager::getInstance()->updatePlayerMoneyData($args[0]);
                        }else{
                            $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.not-enough-money"));
                        }
                    }else{
                        $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.invalid-amount"));
                    }
                }else{
                    $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.player-not-found"));
                }
            }else{
                $this->sendUsageMessage($sender);
            }
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}