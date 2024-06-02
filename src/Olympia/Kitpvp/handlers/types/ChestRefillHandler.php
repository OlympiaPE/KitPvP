<?php

namespace Olympia\Kitpvp\handlers\types;

use Olympia\Kitpvp\handlers\Handler;
use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\block\tile\Chest;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\Server;

final class ChestRefillHandler extends Handler
{
    private array $chestsRefill;

    public function onLoad(): void
    {
        $chestsRefill = [0 => [], 1 => []];
        $world = Server::getInstance()->getWorldManager()->getWorldByName(Managers::CONFIG()->getNested("spawn.world"));
        $loader = Loader::getInstance();

        foreach (Managers::CONFIG()->get("chest-refill") as $id => $chestRefillInfos) {
            if (isset($chestRefillInfos["level"]) && isset($chestRefillInfos["position"])) {
                if ($chestRefillInfos["level"] == 1 || $chestRefillInfos["level"] == 2) {
                    if (
                        isset($chestRefillInfos["position"]["x"]) &&
                        isset($chestRefillInfos["position"]["y"]) &&
                        isset($chestRefillInfos["position"]["z"])
                    ) {
                        $position = new Vector3(
                            (int)$chestRefillInfos["position"]["x"],
                            (int)$chestRefillInfos["position"]["y"],
                            (int)$chestRefillInfos["position"]["z"]
                        );

                        if ($world->getTile($position) instanceof Chest) {

                            $chestsRefill[(int)$chestRefillInfos["level"]][] = $chestRefillInfos["position"];

                            Managers::FLOATING_TEXT()->createFloatingText(
                                Managers::FLOATING_TEXT()->getLocationByCoordinates(
                                    (int)$chestRefillInfos["position"]["x"],
                                    (int)$chestRefillInfos["position"]["y"] + 1,
                                    (int)$chestRefillInfos["position"]["z"]
                                ),
                                $chestRefillInfos["level"] == 1
                                    ? "§7Coffre refill §6- §bPalier 1"
                                    : "§7Coffre refill §6- §ePalier 2"
                            );
                        }else{
                            $loader->getLogger()->error("Le block du chestrefill avec l'identifiant $id n'est pas un coffre !");
                        }
                    }else{
                        $loader->getLogger()->error("Le chestrefill avec l'identifiant $id a un problème avec sa position !");
                    }
                }else{
                    $loader->getLogger()->error("Le chestrefill avec l'identifiant $id a un problème avec son palier/niveau, celui-ci doit être égale à 1 ou 2 !");
                }
            }else{
                $loader->getLogger()->error("Le chestrefill avec l'identifiant $id a un problème avec ses informations, il doit y avoir son niveau ainsi que sa position !");
            }
        }
        $this->chestsRefill = $chestsRefill;
    }

    public function getChestsRefillByLevel(int $level): array
    {
        return $this->chestsRefill[$level];
    }

    public function getChestRefillLootsByLevel(int $level): array
    {
        return match ($level) {
            1 => [
                ["luckRate" => 8, "item" => VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_REGENERATION())],
                ["luckRate" => 8, "item" => VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_POISON())],
                ["luckRate" => 8, "item" => VanillaItems::SPLASH_POTION()->setType(PotionType::WEAKNESS())],
                ["luckRate" => 8, "item" => VanillaItems::SPLASH_POTION()->setType(PotionType::SLOWNESS())],
                ["luckRate" => 1, "item" => VanillaItems::ENDER_PEARL()],
                ["luckRate" => 12, "item" => VanillaItems::GOLDEN_APPLE()->setCount(2)],
                ["luckRate" => 7, "item" => VanillaItems::DIAMOND_HELMET()],
                ["luckRate" => 7, "item" => VanillaItems::DIAMOND_CHESTPLATE()],
                ["luckRate" => 7, "item" => VanillaItems::DIAMOND_LEGGINGS()],
                ["luckRate" => 7, "item" => VanillaItems::DIAMOND_BOOTS()],
                ["luckRate" => 9, "item" => VanillaItems::POTION()->setType(PotionType::LONG_SWIFTNESS())],
                ["luckRate" => 3, "item" => VanillaItems::POTION()->setType(PotionType::STRONG_SWIFTNESS())],
                ["luckRate" => 3, "item" => VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS()))],
                ["luckRate" => 2, "item" => VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2))],
                ["luckRate" => 3, "item" => VanillaItems::DIAMOND_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()))],
                ["luckRate" => 2, "item" => VanillaItems::DIAMOND_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()))],
                ["luckRate" => 2, "item" => VanillaItems::DIAMOND_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()))],
                ["luckRate" => 3, "item" => VanillaItems::DIAMOND_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()))],
            ],
            2 => [
                ["luckRate" => 4, "item" => VanillaItems::DIAMOND_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2))],
                ["luckRate" => 4, "item" => VanillaItems::DIAMOND_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2))],
                ["luckRate" => 4, "item" => VanillaItems::DIAMOND_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2))],
                ["luckRate" => 4, "item" => VanillaItems::DIAMOND_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2))],
                ["luckRate" => 4, "item" => VanillaItems::FISHING_ROD()],
                ["luckRate" => 3.5, "item" => VanillaItems::ENDER_PEARL()->setCount(2)],
                ["luckRate" => 2, "item" => VanillaItems::ENDER_PEARL()->setCount(4)],
                ["luckRate" => 1, "item" => VanillaItems::POTION()->setType(PotionType::STRENGTH())],
                ["luckRate" => 1.5, "item" => VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 5))],
                ["luckRate" => 3, "item" => VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 4))],
                ["luckRate" => 3, "item" => VanillaItems::BOW()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH()))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 5))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY()))],
                ["luckRate" => 3, "item" => VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(6)],
                ["luckRate" => 5, "item" => VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(3)],
                ["luckRate" => 5, "item" => VanillaItems::DIAMOND_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()))],
                ["luckRate" => 5, "item" => VanillaItems::DIAMOND_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()))],
                ["luckRate" => 5, "item" => VanillaItems::DIAMOND_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()))],
                ["luckRate" => 5, "item" => VanillaItems::DIAMOND_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()))],
                ["luckRate" => 7, "item" => VanillaItems::BOW()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY()))],
                ["luckRate" => 4, "item" => VanillaItems::IRON_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3))],
                ["luckRate" => 4, "item" => VanillaItems::IRON_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3))],
                ["luckRate" => 4, "item" => VanillaItems::IRON_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3))],
                ["luckRate" => 4, "item" => VanillaItems::IRON_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3))],
                ["luckRate" => 5, "item" => VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HARMING())],
                ["luckRate" => 5, "item" => VanillaItems::SPLASH_POTION()->setType(PotionType::HARMING())],
                ["luckRate" => 5, "item" => VanillaItems::POTION()->setType(PotionType::LONG_SWIFTNESS())],
            ],
            default => [],
        };
    }

    public function refillChests(int $level): void
    {
        $server = Server::getInstance();
        $world = $server->getWorldManager()->getWorldByName(Managers::CONFIG()->getNested("spawn.world"));

        $chestsRefill = $this->getChestsRefillByLevel($level);

        if (!empty($chestsRefill)) {

            $levelWord = $level == 1 ? "first" : "second";
            $key = "messages.chestrefill-$levelWord-level";
            $server->broadcastMessage(Managers::CONFIG()->getNested($key));

            foreach ($chestsRefill as $chest) {

                $position = new Vector3($chest["x"], $chest["y"], $chest["z"]);
                $tile = $world->getTile($position);

                if ($tile instanceof Chest) {

                    $slots = range(0, 26);
                    $chestContents = [];

                    for ($n = 1; $n <= 4; $n++) {

                        $loots = $this->getChestRefillLootsByLevel($level);
                        $randomNumber = mt_rand(1, 100);
                        $accumulatedLuckRate = 0;

                        foreach ($loots as $lootInfos) {

                            $accumulatedLuckRate += $lootInfos["luckRate"];
                            if ($randomNumber <= $accumulatedLuckRate) {

                                $slot = array_rand($slots);
                                $chestContents[$slot] = $lootInfos["item"];
                                unset($slots[$slot]);
                                break;
                            }
                        }
                    }

                    $tile->getInventory()->setContents($chestContents);
                }
            }
        }
    }
}