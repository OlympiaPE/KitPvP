<?php

namespace Olympia\Kitpvp\commands\moderation;

use DateTimeZone;
use DateTime;
use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\ModerationManager;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class BanCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_BAN;
        parent::__construct("ban", "Ban command", "/ban [player] [duration: 1s/1m/1h/1d/1mo/1y] [reason]");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(count($args) > 2) {

            $playerName = $args[0];
            $time = $args[1];
            $reason = "";
            //2 is the min index for reason
            for ($i = 2; $i < count($args); $i++) {
                $reason .= $args[$i];
                $reason .= " ";
            }
            $reason = substr($reason, 0, strlen($reason) - 1);
            $staff = $sender->getName();

            if(!ModerationManager::getInstance()->isBanned($playerName)) {

                try {
                    $dateTime = new DateTime('now', new DateTimeZone('Europe/Paris'));
                    if(strpos($time, "Y") || strpos($time, "y") || strpos($time, "A") || strpos($time, "a")) {
                        $dateTime->modify("+" . str_replace([" ", "Y", "y", "A", "a"], "", $time) . " year");
                    }elseif(strpos($time, "Mo") || strpos($time, "mo") || strpos($time, "MO") || strpos($time, "mO")) {
                        $dateTime->modify("+" . str_replace([" ", "Mo", "mo", "MO", "mO"], "", $time) . " month");
                    }elseif(strpos($time, "D") || strpos($time, "d") || strpos($time, "j") || strpos($time, "J")) {
                        $dateTime->modify("+" . str_replace([" ", "D", "d", "j", "J"], "", $time) . " day");
                    }elseif(strpos($time, "H") || strpos($time, "h")) {
                        $dateTime->modify("+" . str_replace([" ", "H", "h"], "", $time) . " hour");
                    }elseif(strpos($time, "M") || strpos($time, "m")) {
                        $dateTime->modify("+" . str_replace([" ", "M", "m"], "", $time) . " minute");
                    }elseif(strpos($time, "S") || strpos($time, "s")) {
                        $dateTime->modify("+" . str_replace([" ", "S", "s"], "", $time) . " second");
                    }else{
                        $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.ban-invalid-duration"));
                        return;
                    }
                }catch (Exception) {
                    $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.ban-invalid-duration"));
                    return;
                }

                $serializedDate = $dateTime->format('d/m/Y H:i');
                if(!is_null($player = Server::getInstance()->getPlayerByPrefix($playerName))) {

                    $playerName = $player->getName();
                    $player->kick(str_replace(
                        ["{staff}", "{reason}", "{date}"],
                        [$staff, $reason, $serializedDate],
                        ConfigManager::getInstance()->getNested("messages.ban-kick-screen")
                    ));
                }

                ModerationManager::getInstance()->addBan($playerName, $dateTime, $reason, $staff);
                $sender->getServer()->broadcastMessage(str_replace(
                    ["{player}", "{staff}", "{reason}", "{date}"],
                    [$playerName, $staff, $reason, $serializedDate],
                    ConfigManager::getInstance()->getNested("messages.ban-broadcast-message")
                ));
                WebhookManager::getInstance()->sendMessage("Banissement", "**Joueur** : $playerName\n**Staff** : $staff\n**Raison** : $reason\n*Jusqu'au $serializedDate*", WebhookManager::CHANNEL_LOGS_SANCTIONS);
            }else{
                $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.ban-already-banned"));
            }
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}