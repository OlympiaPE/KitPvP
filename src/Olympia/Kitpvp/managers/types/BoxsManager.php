<?php

namespace Olympia\Kitpvp\managers\types;

use Exception;
use Olympia\Kitpvp\entities\boxs\Box;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\exceptions\CosmeticsException;
use Olympia\Kitpvp\managers\Manager;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use pocketmine\world\Position;

final class BoxsManager extends Manager
{
    public const BOX_VOTE = 0;
    public const BOX_EPIC = 1;
    public const BOX_EVENT = 2;
    public const BOX_SHOP = 3;
    public const BOX_COSMETIC = 4;

    /**
     * @throws CosmeticsException
     */
    public function onLoad(): void
    {
        $boxCosmetics = Managers::COSMETICS()->filterCosmeticsInfosByObtainPrefix(CosmeticsManager::OBTAIN_PREFIX_BOX);
        $totalLuckRate = 0;
        foreach ($boxCosmetics as $cosmeticInfos) {
            $totalLuckRate += $cosmeticInfos["obtain"][CosmeticsManager::OBTAIN_PREFIX_BOX];
        }

        if($totalLuckRate !== 100) {
            throw new CosmeticsException("La somme totale des pourcentages de chance des cosmétiques pour la box Cosmétique n'est pas égale à 100 ! (Obtenu : $totalLuckRate)", CosmeticsException::ERR_TOTAL_LUCK_RATE);
        }
    }

    /**
     * @throws Exception
     */
    public function spawnBox(int $box, Position $pos, int $orientation): void
    {
        $boxName = $this->getBoxName($box);
        $location = new Location($pos->getFloorX() + 0.5, $pos->getFloorY(), $pos->getFloorZ() + 0.5, $pos->getWorld(), $orientation, 0);
        Managers::ENTITIES()->spawnEntity($boxName, $location);
    }

    public function deleteBox(int $box): void
    {
        $boxName = $this->getBoxName($box);
        foreach(Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            foreach($world->getEntities() as $entity) {
                if($entity instanceof Box) {
                    if($entity->getName() === $boxName) {
                        $entity->close();
                    }
                }
            }
        }
    }

    public function getBoxName(int $box): ?string
    {
        return match ($box) {
            $this::BOX_VOTE => "VoteBox",
            $this::BOX_EPIC => "EpiqueBox",
            $this::BOX_EVENT => "EventBox",
            $this::BOX_SHOP => "BoutiqueBox",
            $this::BOX_COSMETIC => "CosmeticBox",
            default => null
        };
    }

    public function giveKey(Session $player, int $box, int $count = 1): void
    {
        $item = $this->getKeyItem($box, $count);
        if(is_null($item)) return;
        $player->safeGiveItem($item);
        $message = Managers::CONFIG()->getNested("messages.receives-keys");
        $message = str_replace(["{quantity}", "{box}"], [$count, $this->getKeyName($box)], $message);
        $player->sendMessage($message);
    }

    public function getKeyItem(int $box, int $count = 1): ?Item
    {
        $item = VanillaItems::HEART_OF_THE_SEA();
        $item->setCount($count);
        $item->setCustomName($this->getKeyName($box));
        return $this->isKey($item) ? $item : null;
    }

    public function getKeyName(int $box): string
    {
        match ($box) {
            $this::BOX_VOTE => $name = "§r§aVote",
            $this::BOX_EPIC => $name = "§r§dEpique",
            $this::BOX_EVENT => $name = "§r§bEvent",
            $this::BOX_SHOP => $name = "§r§eBoutique",
            $this::BOX_COSMETIC => $name = "§r§cCosmétique",
            default => $name = ""
        };
        return $name;
    }

    public function isKey(Item $item): bool
    {
        if($item->getTypeId() === ItemTypeIds::HEART_OF_THE_SEA) {

            $keys = ["§r§aVote", "§r§dEpique", "§r§bEvent", "§r§eBoutique", "§r§8Items", "§r§cCosmétique"];
            return in_array($item->getCustomName(), $keys);
        }
        return false;
    }

    public function useKey(Session $player, int $box): void
    {
        $item = VanillaBlocks::BARRIER()->asItem();

        if(!$player->getInventory()->canAddItem($item)) {

            $player->sendMessage(Managers::CONFIG()->getNested("messages.no-room-in-inventory"));
            return;
        }

        $item = VanillaItems::AIR();

        switch ($box) {

            case $this::BOX_VOTE:

                switch ($luck = mt_rand(1, 1000)) {

                    case $luck >= 1 && $luck <= 10:
                        $player->safeGiveItem(VanillaItems::ENDER_PEARL()->setCount(5));
                        break;

                    case $luck >= 11 && $luck <= 22:
                        $player->safeGiveItem(VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(10));
                        break;

                    case $luck >= 23 && $luck <= 32:
                        $player->safeGiveItem($this->getKeyItem($this::BOX_SHOP, 5));
                        break;

                    case $luck >= 33 && $luck <= 43:
                        $player->safeGiveItem($this->getKeyItem($this::BOX_SHOP));
                        break;

                    case $luck >= 44 && $luck <= 67:
                        $player->safeGiveItem($this->getKeyItem($this::BOX_VOTE, 3));
                        break;

                    case $luck >= 68 && $luck <= 132:
                        $player->safeGiveItem($this->getKeyItem($this::BOX_VOTE));
                        break;

                    case $luck >= 133 && $luck <= 147:
                        $player->safeGiveItem(VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 4)));
                        break;

                    case $luck >= 148 && $luck <= 161:
                        $player->safeGiveItem(VanillaItems::BOW()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH()))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 5))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY())));
                        break;

                    case $luck >= 162 && $luck <= 184:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_ZEUS);
                        break;

                    case $luck >= 185 && $luck <= 209:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_POSEIDON);
                        break;

                    case $luck >= 210 && $luck <= 249:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_ARCHANGES);
                        break;

                    case $luck >= 250 && $luck <= 309:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_ANGES);
                        break;

                    case $luck >= 310 && $luck <= 385:
                        $player->safeGiveItem(VanillaItems::DIAMOND_HELMET(), 2);
                        $player->safeGiveItem(VanillaItems::DIAMOND_CHESTPLATE(), 2);
                        $player->safeGiveItem(VanillaItems::DIAMOND_LEGGINGS(), 2);
                        $player->safeGiveItem(VanillaItems::DIAMOND_BOOTS(), 2);
                        $player->safeGiveItem(VanillaItems::DIAMOND_SWORD(), 2);
                        break;

                    case $luck >= 386 && $luck <= 420:
                        $player->addMoney(10000);
                        break;

                    case $luck >= 421 && $luck <= 475:
                        $player->addMoney(3000);
                        break;

                    case $luck >= 476 && $luck <= 550:
                        $player->addMoney(1000);
                        break;

                    case $luck >= 551 && $luck <= 585:
                        $player->safeGiveItem(VanillaItems::GOLDEN_APPLE()->setCount(64));
                        break;

                    case $luck >= 586 && $luck <= 630:
                        $player->safeGiveItem(VanillaItems::GOLDEN_APPLE()->setCount(32));
                        break;

                    case $luck >= 631 && $luck <= 690:
                        $player->safeGiveItem(VanillaItems::POTION()->setType(PotionType::LONG_FIRE_RESISTANCE), 2);
                        break;

                    case $luck >= 691 && $luck <= 767:
                        $player->safeGiveItem(VanillaItems::POTION()->setType(PotionType::STRONG_HEALING), 3);
                        break;

                    case $luck >= 768 && $luck <= 843:
                        $player->safeGiveItem(VanillaItems::POTION()->setType(PotionType::STRONG_REGENERATION), 3);
                        break;

                    case $luck >= 844 && $luck <= 920:
                        $player->safeGiveItem(VanillaItems::POTION()->setType(PotionType::STRONG_SWIFTNESS), 3);
                        break;

                    case $luck >= 921 && $luck <= 996:
                        $player->safeGiveItem(VanillaItems::FISHING_ROD(), 5);
                        break;

                    case $luck >= 997 && $luck <= 1000:
                        $player->safeGiveItem(VanillaItems::POTION()->setType(PotionType::STRONG_STRENGTH));
                        break;
                }
                break;

            case $this::BOX_EPIC:


                break;

            case $this::BOX_EVENT:

                switch ($luck = mt_rand(1, 1000)) {

                    case $luck >= 1 && $luck <= 100:
                        $player->safeGiveItem(VanillaItems::GOLDEN_APPLE()->setCount(64));
                        break;

                    case $luck >= 101 && $luck <= 200:
                        $player->safeGiveItem(VanillaItems::FISHING_ROD(), 3);
                        break;

                    case $luck >= 201 && $luck <= 300:
                        $player->safeGiveItem(VanillaItems::DIAMOND_HELMET(), 2);
                        $player->safeGiveItem(VanillaItems::DIAMOND_CHESTPLATE(), 2);
                        $player->safeGiveItem(VanillaItems::DIAMOND_LEGGINGS(), 2);
                        $player->safeGiveItem(VanillaItems::DIAMOND_BOOTS(), 2);
                        $player->safeGiveItem(VanillaItems::DIAMOND_SWORD(), 2);
                        break;

                    case $luck >= 301 && $luck <= 400:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_DIABLOTINS);
                        break;

                    case $luck >= 401 && $luck <= 500:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_ARCHANGES);
                        break;

                    case $luck >= 501 && $luck <= 580:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_HECATE);
                        break;

                    case $luck >= 581 && $luck <= 640:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_HADES);
                        break;

                    case $luck >= 641 && $luck <= 665:
                        $player->safeGiveItem(VanillaItems::ENCHANTED_BOOK()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 4)));
                        break;

                    case $luck >= 666 && $luck <= 690:
                        $player->safeGiveItem(VanillaItems::BOW()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH()))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 5))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY())));
                        break;

                    case $luck >= 691 && $luck <= 790:
                        $player->addMoney(2000);
                        break;

                    case $luck >= 791 && $luck <= 860:
                        $player->addMoney(5000);
                        break;

                    case $luck >= 861 && $luck <= 880:
                        $player->addMoney(10000);
                        break;

                    case $luck >= 881 && $luck <= 900:
                        $player->safeGiveItem(VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(3));
                        break;

                    case $luck >= 901 && $luck <= 920:
                        $player->safeGiveItem(VanillaItems::ENCHANTED_BOOK()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5)));
                        break;

                    case $luck >= 921 && $luck <= 940:
                        $player->safeGiveItem($this->getKeyItem($this::BOX_EVENT, 3));
                        break;

                    case $luck >= 941 && $luck <= 965:
                        $player->safeGiveItem($this->getKeyItem($this::BOX_SHOP));
                        break;

                    case $luck >= 966 && $luck <= 980:
                        $player->safeGiveItem($this->getKeyItem($this::BOX_SHOP, 3));
                        break;

                    case $luck >= 981 && $luck <= 1000:
                        $player->safeGiveItem(VanillaItems::ENDER_PEARL()->setCount(2));
                        break;
                }
                break;

            case $this::BOX_SHOP:

                switch ($luck = mt_rand(1, 1000)) {

                    case $luck >= 1 && $luck <= 35:
                        $player->safeGiveItem(VanillaItems::POTION()->setType(PotionType::STRENGTH), 2);
                        break;

                    case $luck >= 36 && $luck <= 90:
                        $player->safeGiveItem(VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING), 2);
                        break;

                    case $luck >= 91 && $luck <= 130:
                        $player->safeGiveItem(VanillaItems::GOLDEN_APPLE()->setCount(64), 4);
                        $player->safeGiveItem(VanillaItems::GOLDEN_APPLE()->setCount(44));
                        break;

                    case $luck >= 131 && $luck <= 174:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_ARCHANGES);
                        break;

                    case $luck >= 175 && $luck <= 209:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_ARCHANGES, 2);
                        break;

                    case $luck >= 210 && $luck <= 239:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_ARCHANGES, 3);
                        break;

                    case $luck >= 240 && $luck <= 284:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_POSEIDON);
                        break;

                    case $luck >= 285 && $luck <= 321:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_POSEIDON, 2);
                        break;

                    case $luck >= 322 && $luck <= 353:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_POSEIDON, 3);
                        break;

                    case $luck >= 354 && $luck <= 396:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_ZEUS);
                        break;

                    case $luck >= 397 && $luck <= 434:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_ZEUS, 2);
                        break;

                    case $luck >= 435 && $luck <= 467:
                        Managers::KITS()->givePlayerKit($player, KitsManager::KIT_ZEUS, 3);
                        break;

                    case $luck >= 468 && $luck <= 500:
                        $player->safeGiveItem(VanillaItems::DIAMOND_SWORD()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 4))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT())));
                        break;

                    case $luck >= 501 && $luck <= 533:
                        $player->safeGiveItem(VanillaItems::ENDER_PEARL()->setCount(5));
                        break;

                    case $luck >= 534 && $luck <= 555:
                        $player->safeGiveItem(VanillaItems::ENDER_PEARL()->setCount(10));
                        break;

                    case $luck >= 556 && $luck <= 573:
                        $player->safeGiveItem(VanillaItems::ENDER_PEARL()->setCount(16));
                        break;

                    case $luck >= 574 && $luck <= 633:
                        $player->addMoney(10000);
                        break;

                    case $luck >= 634 && $luck <= 671:
                        $player->addMoney(25000);
                        break;

                    case $luck >= 672 && $luck <= 707:
                        $player->addMoney(50000);
                        break;

                    case $luck >= 708 && $luck <= 751:
                        $player->safeGiveItem(VanillaItems::BOW()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH()))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 5))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY())));
                        break;

                    case $luck >= 752 && $luck <= 794:
                        $player->safeGiveItem(VanillaItems::DIAMOND_SWORD()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 5))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT())));
                        break;

                    case $luck >= 795 && $luck <= 819:
                        $player->safeGiveItem(VanillaItems::DIAMOND_SWORD()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 5))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT())));
                        $player->safeGiveItem(VanillaItems::BOW()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH()))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 5))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY())));
                        break;

                    case $luck >= 820 && $luck <= 852:
                        $player->safeGiveItem($this->getKeyItem($this::BOX_SHOP));
                        break;

                    case $luck >= 853 && $luck <= 872:
                        $player->safeGiveItem($this->getKeyItem($this::BOX_SHOP, 3));
                        break;

                    case $luck >= 873 && $luck <= 889:
                        $player->safeGiveItem($this->getKeyItem($this::BOX_SHOP, 5));
                        break;

                    case $luck >= 890 && $luck <= 923:
                        $player->safeGiveItem(VanillaItems::DIAMOND_HELMET()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)));
                        $player->safeGiveItem(VanillaItems::DIAMOND_CHESTPLATE()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)));
                        $player->safeGiveItem(VanillaItems::DIAMOND_LEGGINGS()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)));
                        $player->safeGiveItem(VanillaItems::DIAMOND_BOOTS()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)));
                        break;

                    case $luck >= 924 && $luck <= 950:
                        $player->safeGiveItem(VanillaItems::DIAMOND_HELMET()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)), 2);
                        $player->safeGiveItem(VanillaItems::DIAMOND_CHESTPLATE()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)), 2);
                        $player->safeGiveItem(VanillaItems::DIAMOND_LEGGINGS()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)), 2);
                        $player->safeGiveItem(VanillaItems::DIAMOND_BOOTS()
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)), 2);
                        break;

                    case $luck >= 951 && $luck <= 1000:
                        $player->safeGiveItem(VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(5));
                        break;
                }
                break;

            case $this::BOX_COSMETIC:

                $boxCosmetics = Managers::COSMETICS()->filterCosmeticsInfosByObtainPrefix(CosmeticsManager::OBTAIN_PREFIX_BOX);

                $randomNumber = mt_rand(1, 100);
                $accumulatedLuckRate = 0;

                foreach ($boxCosmetics as $cosmetic => $cosmeticInfos) {

                    $accumulatedLuckRate += $cosmeticInfos["obtain"][CosmeticsManager::OBTAIN_PREFIX_BOX];
                    if ($randomNumber <= $accumulatedLuckRate) {
                        $player->addCosmetic($cosmeticInfos["category"], $cosmetic);
                        $player->sendMessage(str_replace("{cosmetic}", $cosmeticInfos["displayName"], Managers::CONFIG()->getNested("messages.obtains-cosmetic")));
                        break;
                    }
                }
                break;
        }

        if($player->getInventory()->canAddItem($item)) {

            $player->sendMessage(Managers::CONFIG()->getNested("messages.open-box"));

            $player->getInventory()->addItem($item);

            $key = $player->getInventory()->getItemInHand();
            $key->setCount($key->getCount() - 1);
            $player->getInventory()->setItemInHand($key);
        }else{

            $player->sendMessage(Managers::CONFIG()->getNested("messages.no-room-in-inventory"));
        }
    }
}