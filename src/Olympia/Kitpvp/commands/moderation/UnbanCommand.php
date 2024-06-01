<?php

namespace Olympia\Kitpvp\commands\moderation;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\ModerationManager;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;

class UnbanCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_UNBAN;
        parent::__construct("unban", "Unban command", "/unban [player]");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(isset($args[0])) {

            $playerName = $args[0];

            if(ModerationManager::getInstance()->isBanned($playerName)) {

                $staff = $sender->getName();
                ModerationManager::getInstance()->removeBan($playerName);

                $sender->sendMessage(str_replace(
                    "{player}",
                    $playerName,
                    ConfigManager::getInstance()->getNested("messages.unban")
                ));

                WebhookManager::getInstance()->sendMessage("Débanissement", "**Joueur** : $playerName\n**Staff** : $staff", WebhookManager::CHANNEL_LOGS_SANCTIONS);
            }else{
                $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.unban-not-banned"));
            }
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}