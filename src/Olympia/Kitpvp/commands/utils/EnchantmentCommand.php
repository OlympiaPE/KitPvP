<?php

namespace Olympia\Kitpvp\commands\utils;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\menu\forms\EnchantForm;
use pocketmine\command\CommandSender;

class EnchantmentCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("enchantement", "Enchantment command", "/enchantement");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof Session) {
            EnchantForm::sendBaseMenu($sender);
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}