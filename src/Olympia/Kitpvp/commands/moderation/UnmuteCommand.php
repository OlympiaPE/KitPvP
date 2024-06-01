<?php

namespace Olympia\Kitpvp\commands\moderation;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\ModerationManager;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class UnmuteCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_UNMUTE;
        parent::__construct("unmute", "Unmute command", "/unmute [player]");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(isset($args[0])) {

            $playerName = $args[0];

            if(ModerationManager::getInstance()->isMute($playerName)) {

                ModerationManager::getInstance()->removeMute($playerName);
                $sender->sendMessage(str_replace(
                    "{player}",
                    $playerName,
                    ConfigManager::getInstance()->getNested("messages.unmute-staff")
                ));

                $staff = $sender->getName();

                if(!is_null($player = Server::getInstance()->getPlayerExact($playerName))) {
                    $player->sendMessage(str_replace(
                        "{staff}",
                        $staff,
                        ConfigManager::getInstance()->getNested("messages.unmute-victim")
                    ));
                }

                WebhookManager::getInstance()->sendMessage("Unmute", "**Joueur** : $playerName\n**Staff** : $staff", WebhookManager::CHANNEL_LOGS_SANCTIONS);
            }else{
                $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.unmute-not-mute"));
            }
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}