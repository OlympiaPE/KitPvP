<?php

namespace Olympia\Kitpvp\commands\utils;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\VoteManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class VoteCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("vote", "Vote command", "/vote");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof Player) {
            VoteManager::getInstance()->testVote($sender);
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}