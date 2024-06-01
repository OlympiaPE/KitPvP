<?php

namespace Olympia\Kitpvp\commands\moderation;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\ModerationManager;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;

class FreezeCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_FREEZE;
        parent::__construct("freeze", "Freeze command", "/freeze [player]");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(isset($args[0])) {

            $playerName = $args[0];

            /** @var $player ?OlympiaPlayer */
            if(!is_null($player = $sender->getServer()->getPlayerByPrefix($playerName))) {

                if(!ModerationManager::getInstance()->isFreeze($player)) {

                    $playerName = $player->getName();
                    $staff = $sender->getName();
                    $sender->sendMessage(str_replace(
                        "{player}",
                        $playerName,
                        ConfigManager::getInstance()->getNested("messages.freeze-staff")
                    ));

                    ModerationManager::getInstance()->addFreeze($player);
                    $player->sendMessage(str_replace(
                        "{staff}",
                        $staff,
                        ConfigManager::getInstance()->getNested("messages.freeze-victim")
                    ));
                    WebhookManager::getInstance()->sendMessage("Freeze", "**Joueur** : $playerName\n**Staff** : $staff", WebhookManager::CHANNEL_LOGS_SANCTIONS);
                }else{
                    $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.freeze-already-freeze"));
                }
            }else{
                $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.player-not-found"));
            }
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}