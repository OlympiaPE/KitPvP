<?php

namespace Olympia\Kitpvp\commands\moderation;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class RtpCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_RTP;
        parent::__construct("rtp", "Rtp command", "/rtp", ['randomtp']);
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof OlympiaPlayer) {

            $players = Server::getInstance()->getOnlinePlayers();
            unset($players[array_search($sender, $players)]);

            if(empty($players)) {
                $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.rtp-no-player"));
            }else{
                $rtpPlayer = $players[array_rand($players)];

                $sender->teleport($rtpPlayer->getPosition());
                $sender->sendMessage(str_replace(
                    "{player}",
                    $rtpPlayer->getName(),
                    ConfigManager::getInstance()->getNested("messages.rtp")
                ));
            }
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}