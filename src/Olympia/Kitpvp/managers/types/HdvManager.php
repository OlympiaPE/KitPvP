<?php

namespace Olympia\Kitpvp\managers\types;

use Exception;
use JsonException;
use DateTimeZone;
use DateTime;
use Olympia\Kitpvp\Core;
use Olympia\Kitpvp\managers\ManageLoader;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

final class HdvManager extends ManageLoader
{
    use SingletonTrait;

    public array $purchasableItems = [];
    private int $totalPurchasableItemsCount = 0;

    private array $config;

    /**
     */
    public function onInit(): void
    {
        try {
            @mkdir(Core::getInstance()->getDataFolder() . "/data/");
            $this->config = (new Config(Core::getInstance()->getDataFolder() . $this->getHdvFile(), Config::JSON))->getAll();
            $this->purchasableItems = $this->getHdvConfig();

            foreach ($this->purchasableItems as $seller => $items) {
                foreach ($items as $itemProperties) {
                    if(!$itemProperties["expired"]) {
                        $this->totalPurchasableItemsCount += $this->getNumberPurchasablePlayerItems($seller);
                    }
                }
            }
        }catch (Exception) {
            Server::getInstance()->getLogger()->alert("§cLe core n'a pas la permission de créer le fichier {$this->getHdvFile()}. Veuillez le faire et redémarrez le serveur.");
            $this->config = [];
        }
    }

    /**
     * @throws JsonException
     */
    public function onDisable(): void
    {
        $this->saveAllHdv();
        parent::onDisable();
    }

    public function getPurchasableItems(int $page): array
    {
        $itemsArray = [];
        foreach ($this->purchasableItems as $seller => $items) {
            foreach ($items as $itemProperties) {
                if(!$itemProperties["expired"]) {
                    $item = Item::nbtDeserialize(unserialize($itemProperties["item"]));
                    $item->setLore(["§fVendeur : §6$seller", "§fPrix : §6{$itemProperties["price"]}$"]);
                    $itemsArray[] = $item;
                }
            }
        }

        return array_filter($itemsArray, function($key) use ($page) {
            return $key < 36 * $page && $key >= 36 * --$page;
        }, ARRAY_FILTER_USE_KEY);
    }

    public function getUsefulItems(string $player): array
    {
        $items = [];
        $items[45] = VanillaItems::DIAMOND()->setCustomName("§9Vos items {$this->getNumberPurchasablePlayerItems($player)}/5");
        $items[48] = VanillaItems::PAPER()->setCustomName("Page précédente");
        $items[49] = VanillaItems::BOOK()->setCustomName("/hdv sell [prix] pour vendre");
        $items[50] = VanillaItems::PAPER()->setCustomName("Page suivante");
        $items[53] = VanillaBlocks::ENDER_CHEST()->asItem()->setCustomName("§cVos invendus {$this->getNumberExpiredPlayerItems($player)}/5");
        return $items;
    }

    public function getMaxPage(): int
    {
        return ceil($this->totalPurchasableItemsCount / 36) > 0 ? ceil($this->totalPurchasableItemsCount / 36) : 1;
    }

    /**
     * @throws Exception
     */
    public function addPurchasableItemForPlayer(string $player, Item $item, int $price): void
    {
        $this->totalPurchasableItemsCount++;
        $date = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $this->purchasableItems[strtolower($player)][] = [
            "item" => serialize($item->nbtSerialize()),
            "price" => $price,
            "expired" => false,
            "date" => $date->format("d/m/Y H:i"),
        ];
    }

    public function removeItem(string $player, Item $item): void
    {
        foreach ($this->purchasableItems[strtolower($player)] as $key => $itemProperties) {
            if($item->getName() === Item::nbtDeserialize(unserialize($itemProperties["item"]))->getName()) {
                if(!$itemProperties["expired"]) {
                    $this->totalPurchasableItemsCount--;
                }
                unset($this->purchasableItems[strtolower($player)][$key]);
                break;
            }
        }
    }

    public function isItemStillAvailable(string $seller, Item $item): bool
    {
        foreach ($this->purchasableItems[strtolower($seller)] as $itemProperties) {
            if($item->getName() === Item::nbtDeserialize(unserialize($itemProperties["item"]))->getName()) {
                if(!$itemProperties["expired"]) {
                    return true;
                }
            }
        }
        return false;
    }

    public function setItemExpired(string $player, int $key): void
    {
        $this->totalPurchasableItemsCount--;
        $this->purchasableItems[$player][$key]["expired"] = true;
    }

    public function getNumberExpiredPlayerItems(string $player): int
    {
        if(isset($this->purchasableItems[strtolower($player)])) {
            $number = 0;
            foreach($this->purchasableItems[strtolower($player)] as $item) {
                if($item["expired"]) {
                    $number++;
                }
            }
            return $number;
        }else return 0;
    }

    public function getExpiredPlayerItem(string $player): ?string
    {
        if(isset($this->purchasableItems[strtolower($player)])) {
            foreach ($this->purchasableItems[strtolower($player)] as $item) {
                if($item["expired"]) {
                    return $item["item"];
                }
            }
        }
        return null;
    }

    public function getNumberPurchasablePlayerItems(string $player): int
    {
        if(isset($this->purchasableItems[strtolower($player)])) {
            $number = 0;
            foreach($this->purchasableItems[strtolower($player)] as $item) {
                if(!$item["expired"]) {
                    $number++;
                }
            }
            return $number;
        }else return 0;
    }

    /**
     * @throws JsonException
     */
    public function saveAllHdv(): void
    {
        $config = new Config(Core::getInstance()->getDataFolder() . $this->getHdvFile(), Config::JSON);
        $config->setAll($this->purchasableItems);
        $config->save();
    }

    public function getHdvConfig(): array
    {
        return $this->config;
    }

    public function getHdvFile(): string
    {
        return "/data/HdvContent.json";
    }
}