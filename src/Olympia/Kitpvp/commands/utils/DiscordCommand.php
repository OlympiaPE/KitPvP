<?php

namespace Olympia\Kitpvp\commands\utils;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\command\CommandSender;

class DiscordCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("discord", "Discord command", "/discord");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $sender->sendMessage(Managers::CONFIG()->getNested("messages.discord"));
    }
}