<?php

namespace Olympia\Kitpvp\commands;

use Olympia\Kitpvp\managers\Managers;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissions;

abstract class OlympiaCommand extends Command
{
    public string $permission = DefaultPermissions::ROOT_USER;

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission($this->permission);
        $this->setPermissionMessage(Managers::CONFIG()->getNested("messages.not-allowed"));
    }

    public function sendNotPlayerMessage(CommandSender $sender): void
    {
        $sender->sendMessage(Managers::CONFIG()->getNested("messages.not-a-player"));
    }

    public function sendUsageMessage(CommandSender $sender): void
    {
        $sender->sendMessage(str_replace("{commandUsage}", $this->getUsage(), Managers::CONFIG()->getNested("messages.command-args-error")));
    }
}