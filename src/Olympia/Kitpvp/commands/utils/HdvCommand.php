<?php

namespace Olympia\Kitpvp\commands\utils;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\menu\gui\HdvGui;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;

class HdvCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("hdv", "Hdv command", "/hdv sell [prix]", ['ah']);
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof Session) {

            if(isset($args[0]) && ($args[0] === "sell" || $args[0] === "vendre")) {
                $item = $sender->getInventory()->getItemInHand();
                if($item->getTypeId() != VanillaItems::AIR()->getTypeId() && isset($args[1]) && is_numeric($args[1]) && !str_contains($args[1], '.')) {
                    $price = (int)$args[1];
                    if($price > 0 && $price <= 500000000) {
                        $itemsPCount = Managers::HDV()->getNumberPurchasablePlayerItems($sender->getName());
                        $itemsECount = Managers::HDV()->getNumberExpiredPlayerItems($sender->getName());
                        if(($itemsPCount + $itemsECount) < 5) {
                            Managers::HDV()->addPurchasableItemForPlayer($sender->getName(), $item, $price);
                            $sender->getInventory()->setItemInHand(VanillaItems::AIR());
                            $sender->sendMessage(str_replace(
                                ["{item}", "{price}"],
                                [$item->getName(), $price],
                                Managers::CONFIG()->getNested("messages.hdv-add-item")
                            ));
                        }else{
                            $sender->sendMessage(Managers::CONFIG()->getNested("messages.hdv-max-slot"));
                        }
                    }else{
                        $sender->sendMessage(Managers::CONFIG()->getNested("messages.invalid-amount"));
                    }
                }else{
                    $this->sendUsageMessage($sender);
                }
                return;
            }
            $menu = new HdvGui($sender);
            $menu->send($sender);
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}