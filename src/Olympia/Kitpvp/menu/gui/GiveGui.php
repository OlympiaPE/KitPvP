<?php

namespace Olympia\Kitpvp\menu\gui;

use Closure;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use Olympia\Kitpvp\entities\Session;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\world\sound\ShulkerBoxCloseSound;

class GiveGui
{
    private InvMenu $menu;

    /**
     * @param Item[] $items
     */
    public function __construct(array $items)
    {
        $this->menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST)
            ->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {

                return $transaction->continue();
            })
            ->setInventoryCloseListener(function(Session $viewer, Inventory $inventory): void {

                foreach ($inventory->getContents() as $item) {
                    $viewer->getWorld()->dropItem($viewer->getPosition(), $item);
                }
                $viewer->resetGiveGui();
                $viewer->broadcastSound(new ShulkerBoxCloseSound(), [$viewer]);
            })
            ->setName("");

        $this->menu->getInventory()->setContents($items);
    }

    public function addItems(array $items): void
    {
        $inventory = $this->menu->getInventory();
        $inventory->setContents(array_merge($inventory->getContents(), $items));
    }

    public function send(Player $player, ?string $custom_name = null, ?Closure $callback = null): void
    {
        $this->menu->send($player, $custom_name, $callback);
    }
}