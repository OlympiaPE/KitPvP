<?php

namespace Olympia\Kitpvp\commands\money;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\command\CommandSender;

class MymoneyCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("mymoney", "Mymoney command", "/mymoney");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof Session) {
            $sender->sendMessage(str_replace(
                "{money}",
                $sender->getMoney(),
                Managers::CONFIG()->getNested("messages.my-money")
            ));
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}