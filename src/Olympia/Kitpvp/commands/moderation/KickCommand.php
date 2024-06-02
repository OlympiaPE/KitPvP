<?php

namespace Olympia\Kitpvp\commands\moderation;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\utils\constants\Permissions;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class KickCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_KICK;
        parent::__construct("kick", "Kick command", "/kick [player] [reason]");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(count($args) > 1) {

            $playerName = $args[0];
            $reason = "";
            //1 is the min index for reason
            for ($i = 1; $i < count($args); $i++) {
                $reason .= $args[$i];
                $reason .= " ";
            }
            $reason = substr($reason, 0, strlen($reason) - 1);

            if(!is_null($player = $sender->getServer()->getPlayerByPrefix($playerName))) {

                $playerName = $player->getName();
                $staff = $sender->getName();

                $player->kick(str_replace(
                    ["{staff}", "{reason}"],
                    [$staff, $reason],
                    Managers::CONFIG()->getNested("messages.kick-screen")
                ));

                $sender->sendMessage(str_replace(
                    ["{player}", "{reason}"],
                    [$playerName, $reason],
                    Managers::CONFIG()->getNested("messages.kick-staff")
                ));

                Server::getInstance()->broadcastMessage(str_replace(
                    ["{player}", "{staff}", "{reason}"],
                    [$playerName, $staff, $reason],
                    Managers::CONFIG()->getNested("messages.kick-broadcast-message")
                ));

                Managers::WEBHOOK()->sendMessage("Expulsion", "**Joueur** : $playerName\n**Staff** : $staff\n**Raison** : $reason", WebhookManager::CHANNEL_LOGS_SANCTIONS);
            }else{
                $sender->sendMessage(Managers::CONFIG()->getNested("messages.player-not-found"));
            }
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}