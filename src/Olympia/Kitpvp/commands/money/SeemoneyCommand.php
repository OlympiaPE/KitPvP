<?php

namespace Olympia\Kitpvp\commands\money;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\managers\Managers;
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
            /** @var ?Session $player */
            $player = Server::getInstance()->getPlayerExact($playerName);

            if(is_null($player)) {
                if(!is_null($playerUuid = Managers::DATABASE()->getUuidByUsername($playerName))) {
                    $money = Managers::DATABASE()->getUuidData($playerUuid, "money");
                }else{
                    $sender->sendMessage(Managers::CONFIG()->getNested("messages.player-not-found"));
                    return;
                }
            }else{
                $money = $player->getMoney();
            }

            $sender->sendMessage(str_replace(
                ["{player}", "{money}"],
                [$playerName, (string)$money],
                Managers::CONFIG()->getNested("messages.see-money")
            ));
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}