<?php

namespace Olympia\Kitpvp\commands\money;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\constants\Permissions;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class RemovemoneyCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_REMOVEMONEY;
        parent::__construct("removemoney", "Removemoney command", "/removemoney [player] [money]");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(count($args) > 1 && is_numeric($args[1]) && !str_contains($args[1], '.')) {

            if(
                !is_null(Server::getInstance()->getOfflinePlayerData($args[0])) ||
                Managers::MONEY()->inPlayersMoneyData($args[0])
            ) {

                $money = intval($args[1]);

                if(!is_null($player = Server::getInstance()->getPlayerExact($args[0]))) {
                    /** @var Session $player */
                    $player->removeMoney($money);
                    $sender->sendMessage(str_replace(
                        "{money}",
                        $money,
                        Managers::CONFIG()->getNested("messages.lose-money")
                    ));
                }else{
                    Managers::MONEY()->removeOfflinePlayerMoney($args[0], $money);
                }

                Managers::MONEY()->updatePlayerMoneyData($args[0]);
                $sender->sendMessage(str_replace(
                    ["{player}", "{money}"],
                    [$args[0], (string)$money],
                    Managers::CONFIG()->getNested("messages.remove-money")
                ));
            }else{
                $sender->sendMessage(Managers::CONFIG()->getNested("messages.player-not-found"));
            }
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}