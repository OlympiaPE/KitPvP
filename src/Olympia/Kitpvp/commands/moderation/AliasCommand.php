<?php

namespace Olympia\Kitpvp\commands\moderation;

use DateTime;
use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\constants\Permissions;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class AliasCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_ALIAS;
        parent::__construct("alias", "Alias command", "/alias [player]");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(isset($args[0])) {

            $target = $args[0];
            if(!is_null($player = Server::getInstance()->getPlayerExact($target))) {
                $targetIp = $player->getNetworkSession()->getIp();
            }elseif(!is_null($data = Server::getInstance()->getOfflinePlayerData($target))) {
                $targetIp = $data->getCompoundTag("properties")->getString("ip");
            }else{
                $sender->sendMessage(Managers::CONFIG()->getNested("messages.player-not-found"));
                return;
            }
            $dc = [];
            $path = Server::getInstance()->getDataPath() . "/players";
            foreach (scandir($path) as $file) {
                if ($file != '.' && $file != '..') {
                    $name = pathinfo($file, PATHINFO_FILENAME);
                    $data = Server::getInstance()->getOfflinePlayerData($name);
                    $ip = $data->getCompoundTag("properties")->getString("ip");
                    if($targetIp === $ip) {
                        $lastPlayed = Server::getInstance()->getOfflinePlayer($name)->getLastPlayed();
                        $dateTime = new DateTime('@' . intdiv($lastPlayed, 1000) + 7200);
                        $lastPlayedDate = $dateTime->format("H:i d/m/y");
                        $dc[$name] = $lastPlayedDate;
                    }
                }
            }

            $message = str_replace(
                ["{player}", "{ip}"],
                [$target, $targetIp],
                Managers::CONFIG()->getNested("messages.alias-title")
            );
            foreach ($dc as $name => $lastPlayed) {
                $message .= "\n" . str_replace(
                    ["{pseudo}", "{lastPlayed}"],
                    [$name, $lastPlayed],
                    Managers::CONFIG()->getNested("messages.alias-line")
                );
            }
            $sender->sendMessage($message);
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}