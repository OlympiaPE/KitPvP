<?php

namespace Olympia\Kitpvp\menu\gui;

use Closure;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;

class HdvGui
{
    private InvMenu $menu;

    private int $page;

    private string $player;

    public function __construct(Player $player, int $page = 1)
    {
        $this->page = $page;
        $this->player = strtolower($player->getName());
        $this->menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST)
            ->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult {

                /** @var Session $player */
                $player = $transaction->getPlayer();
                $item = $transaction->getItemClicked();
                $sellerName = $item->getLore()[0] ?? null;
                $price = $item->getLore()[1] ?? null;
                $slot = $transaction->getAction()->getSlot();

                if(!$item->hasCustomName() && empty($item->getLore())) {
                    return $transaction->discard();
                }

                switch ($slot) {

                    case 0:
                    case $slot < 36:

                        $sellerName = str_replace("§fVendeur : §6", "", $sellerName);
                        if(Managers::HDV()->isItemStillAvailable($sellerName, $item)) {

                            $price = str_replace("§6", "", $price);
                            $price = (int)preg_replace("/[^0-9]/", "", $price);
                            if($player->hasEnoughMoney($price)) {

                                if($player->getInventory()->canAddItem($item)) {
                                    $item->setLore([]);
                                    Managers::HDV()->removeItem($sellerName, $item);
                                    if(!is_null($seller = Server::getInstance()->getPlayerExact($sellerName))) {
                                        /** @var Session $seller */
                                        $seller->addMoney($price);
                                        $seller->sendMessage(Managers::CONFIG()->getNested("messages.hdv-item-sell"));
                                    }elseif(!is_null($playerUuid = Managers::DATABASE()->getUuidByUsername($sellerName))) {
                                        $playerMoney = Managers::DATABASE()->getUuidData($playerUuid, "money");
                                        $totalMoney = $playerMoney + $price;
                                        Managers::DATABASE()->setUuidData($playerUuid, "money", $totalMoney);
                                    }

                                    $player->removeMoney($price);
                                    $player->getInventory()->addItem($item);
                                    $player->sendMessage(str_replace(
                                        ["{item}", "{price}"],
                                        [$item->getName(), $price],
                                        Managers::CONFIG()->getNested("messages.hdv-buy-item")
                                    ));
                                }else{
                                    $player->sendMessage(Managers::CONFIG()->getNested("messages.no-room-in-inventory"));
                                }
                            }else{
                                $player->sendMessage(Managers::CONFIG()->getNested("messages.not-enough-money"));
                            }
                        }else{
                            $player->sendMessage(Managers::CONFIG()->getNested("messages.hdv-item-unavailable"));
                        }
                        $player->removeCurrentWindow();
                        break;

                    case 48:

                        if($this->page > 1) {
                            $this->sendPreviousPage();
                        }
                        break;

                    case 50:

                        if($this->page < Managers::HDV()->getMaxPage()) {
                            $this->sendNextPage();
                        }
                        break;

                    case 53:

                        if(!is_null($itemSerialized = Managers::HDV()->getExpiredPlayerItem($player->getName()))) {
                            $item = Item::nbtDeserialize(unserialize($itemSerialized));
                            if($player->getInventory()->canAddItem($item)) {
                                Managers::HDV()->removeItem($player->getName(), $item);
                                $player->getInventory()->addItem($item);
                                $player->sendMessage(str_replace(
                                    "{item}",
                                    $item->getName(),
                                    Managers::CONFIG()->getNested("messages.hdv-remove-unsold-item")
                                ));
                            }else{
                                $player->sendMessage(Managers::CONFIG()->getNested("messages.no-room-in-inventory"));
                            }
                        }else{
                            $player->sendMessage(Managers::CONFIG()->getNested("messages.hdv-no-unsold-item"));
                        }
                        $player->removeCurrentWindow();
                        break;
                }
                return $transaction->discard();
            })
            ->setName("§l§6HDV §7Page $this->page/". Managers::HDV()->getMaxPage());
        $this->addUsefulItems();
        $this->addPurchasableItems($this->page);
    }

    public function send(Player $player, ?string $custom_name = null, ?Closure $callback = null): void
    {
        $this->menu->send($player, $custom_name, $callback);
    }

    public function sendPreviousPage(): void
    {
        $this->page = $this->page - 1;
        $this->updatePurchasableItems();
    }

    public function sendNextPage(): void
    {
        $this->page = $this->page + 1;
        $this->updatePurchasableItems();
    }

    public function updatePurchasableItems(): void
    {
        $this->resetPurchasableItems();
        $this->addPurchasableItems($this->page);
        $this->menu->setName("§l§6HDV §7Page $this->page/". Managers::HDV()->getMaxPage());
        $player = Server::getInstance()->getPlayerExact($this->player);
        if(!is_null($player)) {
            $this->send($player);
        }
    }

    public function addUsefulItems(): void
    {
        foreach (Managers::HDV()->getUsefulItems($this->player) as $slot => $item) {
            $this->menu->getInventory()->setItem($slot, $item);
        }
    }

    public function addPurchasableItems(int $page): void
    {
        foreach (Managers::HDV()->getPurchasableItems($page) as $slot => $item) {
            $this->menu->getInventory()->setItem($slot, $item);
        }
    }

    public function resetPurchasableItems(): void
    {
        foreach ($this->menu->getInventory()->getContents() as $slot => $item) {
            if($slot >= 45) {
                break;
            }
            $this->menu->getInventory()->setItem($slot, VanillaItems::AIR());
        }
    }
}