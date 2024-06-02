<?php

namespace Olympia\Kitpvp\commands\money;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\managers\Managers;
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
        if($sender instanceof Session) {

            if(isset($args[0]) && isset($args[1])) {

                if(
                    !is_null(Server::getInstance()->getOfflinePlayerData($args[0])) ||
                    Managers::MONEY()->inPlayersMoneyData($args[0])
                ) {

                    $money = intval($args[1]);

                    if($money > 0 && is_numeric($args[1]) && !str_contains($args[1], '.')) {

                        if($sender->hasEnoughMoney($money)) {

                            $sender->removeMoney($money);
                            Managers::MONEY()->updatePlayerMoneyData($sender->getName());

                            $sender->sendMessage(str_replace(
                                ["{player}", "{money}"],
                                [$args[0], (string)$money],
                                Managers::CONFIG()->getNested("messages.pay-money")
                            ));

                            if(!is_null($player = Server::getInstance()->getPlayerExact($args[0]))) {

                                /** @var Session $player */
                                $player->addMoney($money);
                                $player->sendMessage(str_replace(
                                    ["{player}", "{money}"],
                                    [$sender->getDisplayName(), (string)$money],
                                    Managers::CONFIG()->getNested("messages.receive-money")
                                ));
                            }else{
                                Managers::MONEY()->addOfflinePlayerMoney($args[0], $money);
                            }
                            Managers::MONEY()->updatePlayerMoneyData($args[0]);
                        }else{
                            $sender->sendMessage(Managers::CONFIG()->getNested("messages.not-enough-money"));
                        }
                    }else{
                        $sender->sendMessage(Managers::CONFIG()->getNested("messages.invalid-amount"));
                    }
                }else{
                    $sender->sendMessage(Managers::CONFIG()->getNested("messages.player-not-found"));
                }
            }else{
                $this->sendUsageMessage($sender);
            }
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}