<?php

namespace Olympia\Kitpvp\commands\moderation;

use DateTime;
use DateTimeZone;
use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\utils\constants\Permissions;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class MuteCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_MUTE;
        parent::__construct("mute", "Mute command", "/mute [player] [duration: 1s/1m/1h/1d/1mo/1y] [reason]");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(count($args) > 2) {

            $time = $args[1];

            if(!is_null($player = $sender->getServer()->getPlayerByPrefix($args[0]))) {

                $playerName = $player->getName();
                if(!Managers::MODERATION()->isMute($playerName)) {

                    $reason = "";
                    //2 is the min index for reason
                    for ($i = 2; $i < count($args); $i++) {
                        $reason .= $args[$i];
                        $reason .= " ";
                    }
                    $reason = substr($reason, 0, strlen($reason) - 1);

                    try {
                        $dateTime = new DateTime('now', new DateTimeZone('Europe/Paris'));
                        if(strpos($time, "D") || strpos($time, "d") || strpos($time, "j") || strpos($time, "J")) {
                            $dateTime->modify("+" . str_replace([" ", "D", "d", "j", "J"], "", $time) . " day");
                        }elseif(strpos($time, "H") || strpos($time, "h")) {
                            $dateTime->modify("+" . str_replace([" ", "H", "h"], "", $time) . " hour");
                        }elseif(strpos($time, "M") || strpos($time, "m")) {
                            $dateTime->modify("+" . str_replace([" ", "M", "m"], "", $time) . " minute");
                        }elseif(strpos($time, "S") || strpos($time, "s")) {
                            $dateTime->modify("+" . str_replace([" ", "S", "s"], "", $time) . " second");
                        }else{
                            $sender->sendMessage(Managers::CONFIG()->getNested("messages.mute-invalid-duration"));
                            return;
                        }
                    }catch (Exception) {
                        $sender->sendMessage(Managers::CONFIG()->getNested("messages.mute-invalid-duration"));
                        return;
                    }

                    $now = new DateTime("now", new DateTimeZone('Europe/Paris'));
                    $diff = $dateTime->diff($now);
                    $duration = $diff->s + ($diff->i * 60) + ($diff->h * 3600) + ($diff->days * 86400) + 1;
                    $staff = $sender->getName();

                    Managers::MODERATION()->addMute($playerName, $duration);

                    $remainingTime = Managers::MODERATION()->getMuteRemainingTime($playerName);

                    Server::getInstance()->broadcastMessage(str_replace(
                        ["{player}", "{staff}", "{reason}", "{remainingTime}"],
                        [$playerName, $staff, $reason, $remainingTime],
                        Managers::CONFIG()->getNested("messages.mute-broadcast-message")
                    ));

                    $player->sendMessage(str_replace(
                        ["{staff}", "{reason}", "{remainingTime}"],
                        [$staff, $reason, $remainingTime],
                        Managers::CONFIG()->getNested("messages.mute-victim")
                    ));

                    Managers::WEBHOOK()->sendMessage("Mute", "**Joueur** : $playerName\n**Staff** : $staff\n**Raison** : $reason\n**Durée** : $remainingTime", WebhookManager::CHANNEL_LOGS_SANCTIONS);
                }else{
                    $sender->sendMessage(Managers::CONFIG()->getNested("messages.mute-already-mute"));
                }
            }else{
                $sender->sendMessage(Managers::CONFIG()->getNested("messages.player-not-found"));
            }
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}