<?php

namespace Olympia\Kitpvp\commands\moderation;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\ModerationManager;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;

class ChatCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_CHAT;
        parent::__construct("chat", "Chat command", "/chat [lock/unlock]");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (isset($args[0]) && (strtolower($args[0]) === "lock" || strtolower($args[0]) === "unlock")) {
            if (strtolower($args[0]) === "lock") {
                ModerationManager::getInstance()->lockChat();
                $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.chat-lock"));
                $sender->getServer()->broadcastMessage(ConfigManager::getInstance()->getNested("messages.chat-lock-broadcast"));
            }else{
                ModerationManager::getInstance()->unlockChat();
                $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.chat-unlock"));
                $sender->getServer()->broadcastMessage(ConfigManager::getInstance()->getNested("messages.chat-unlock-broadcast"));
            }
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}