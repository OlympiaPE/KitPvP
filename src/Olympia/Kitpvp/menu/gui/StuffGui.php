<?php

namespace Olympia\Kitpvp\menu\gui;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use Closure;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;

class StuffGui
{
    private InvMenu $menu;

    public function __construct(Player $target)
    {
        $this->menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST)
            ->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {

                return $transaction->discard();
            })
            ->setName("§6Stuff de {$target->getName()}");

        $contents = $target->getInventory()->getContents();
        $contents[45] = $target->getArmorInventory()->getHelmet();
        $contents[46] = $target->getArmorInventory()->getChestplate();
        $contents[47] = $target->getArmorInventory()->getLeggings();
        $contents[48] = $target->getArmorInventory()->getBoots();
        for($i = 36; $i <= 44; $i++) {
            $item = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::ORANGE())->asItem();
            $contents[$i] = $item;
        }
        for($i = 49; $i <= 53; $i++) {
            $item = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::ORANGE())->asItem();
            $contents[$i] = $item;
        }
        $this->menu->getInventory()->setContents($contents);
    }

    public function send(Player $player, ?string $custom_name = null, ?Closure $callback = null): void
    {
        $this->menu->send($player, $custom_name, $callback);
    }
}