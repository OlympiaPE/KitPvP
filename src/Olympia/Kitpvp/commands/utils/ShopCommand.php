<?php

namespace Olympia\Kitpvp\commands\utils;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\menu\forms\ShopForm;
use pocketmine\command\CommandSender;

class ShopCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("shop", "Shop command", "/shop");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof Session) {
            ShopForm::sendBaseMenu($sender);
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}