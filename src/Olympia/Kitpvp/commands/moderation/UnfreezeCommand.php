<?php

namespace Olympia\Kitpvp\commands\moderation;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\ModerationManager;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;

class UnfreezeCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_UNFREEZE;
        parent::__construct("unfreeze", "Unfreeze command", "/unfreeze [player]");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(isset($args[0])) {

            $playerName = $args[0];

            if(!is_null($player = $sender->getServer()->getPlayerByPrefix($playerName))) {

                $playerName = $player->getName();

                if(ModerationManager::getInstance()->isFreeze($player)) {

                    $sender->sendMessage(str_replace(
                        "{player}",
                        $playerName,
                        ConfigManager::getInstance()->getNested("messages.unfreeze-staff")
                    ));
                    $staff = $sender->getName();

                    ModerationManager::getInstance()->removeFreeze($player);

                    $player->sendMessage(str_replace(
                        "{staff}",
                        $staff,
                        ConfigManager::getInstance()->getNested("messages.unfreeze-victim")
                    ));

                    WebhookManager::getInstance()->sendMessage("Unfreeze", "**Joueur** : $playerName\n**Staff** : $staff", WebhookManager::CHANNEL_LOGS_SANCTIONS);
                }else{
                    $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.unfreeze-not-freeze"));
                }
            }else{
                $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.player-not-found"));
            }
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}