<?php

namespace Olympia\Kitpvp\commands\moderation;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\constants\Permissions;
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
                Managers::MODERATION()->lockChat();
                $sender->sendMessage(Managers::CONFIG()->getNested("messages.chat-lock"));
                $sender->getServer()->broadcastMessage(Managers::CONFIG()->getNested("messages.chat-lock-broadcast"));
            }else{
                Managers::MODERATION()->unlockChat();
                $sender->sendMessage(Managers::CONFIG()->getNested("messages.chat-unlock"));
                $sender->getServer()->broadcastMessage(Managers::CONFIG()->getNested("messages.chat-unlock-broadcast"));
            }
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}