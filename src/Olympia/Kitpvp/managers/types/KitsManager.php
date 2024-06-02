<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\managers\Manager;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;

final class KitsManager extends Manager
{
    public const KIT_REFILL = 0;
    public const KIT_HOURLY = 1;
    public const KIT_DAILY = 2;
    public const KIT_WEEKLY = 3;
    public const KIT_ARCHER = 4;
    public const KIT_JOUEUR = 5;
    public const KIT_ANGES = 6;
    public const KIT_DIABLOTINS = 7;
    public const KIT_ARCHANGES = 8;
    public const KIT_PERSEPHONE = 9;
    public const KIT_POSEIDON = 10;
    public const KIT_HECATE = 11;
    public const KIT_ZEUS = 12;
    public const KIT_HADES = 13;

    public function onLoad(): void
    {
    }

    public function getKitContents(int $kit): array
    {
        $contents = [];

        switch ($kit) {

            case $this::KIT_REFILL:
                $contents[] = VanillaItems::GOLDEN_APPLE()->setCount(64);
                $contents[] = VanillaItems::ENCHANTED_GOLDEN_APPLE();
                $contents[] = VanillaItems::POTION()->setType(PotionType::LONG_SWIFTNESS);
                $contents[] = VanillaItems::POTION()->setType(PotionType::LONG_SWIFTNESS);
                $contents[] = VanillaItems::POTION()->setType(PotionType::LONG_SWIFTNESS);
                $contents[] = VanillaItems::POTION()->setType(PotionType::LONG_SWIFTNESS);
                $contents[] = VanillaItems::POTION()->setType(PotionType::FIRE_RESISTANCE);
                break;

            case $this::KIT_HOURLY:
                $contents[] = VanillaItems::IRON_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3));
                $contents[] = VanillaItems::IRON_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3));
                $contents[] = VanillaItems::IRON_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3));
                $contents[] = VanillaItems::IRON_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3));
                $contents[] = VanillaItems::IRON_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3));
                $contents[] = VanillaItems::POTION()->setType(PotionType::SWIFTNESS);
                $contents[] = VanillaItems::BOW();
                $contents[] = VanillaItems::ARROW()->setCount(64);
                break;

            case $this::KIT_DAILY:
            case $this::KIT_WEEKLY:
                $contents[] = VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS()));
                $contents[] = VanillaItems::DIAMOND_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()));
                $contents[] = VanillaItems::DIAMOND_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()));
                $contents[] = VanillaItems::DIAMOND_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()));
                $contents[] = VanillaItems::DIAMOND_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()));
                $contents[] = VanillaItems::BOW()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH()))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER()));
                $contents[] = VanillaItems::GOLDEN_APPLE()->setCount(10);
                $contents[] = VanillaItems::ENCHANTED_GOLDEN_APPLE();
                $contents[] = VanillaItems::ARROW()->setCount(64);
                $contents[] = VanillaItems::POTION()->setType(PotionType::SWIFTNESS);
                $contents[] = VanillaItems::SPLASH_POTION()->setType(PotionType::POISON);
                break;

            case $this::KIT_ARCHER:
                $contents[] = VanillaItems::STONE_SWORD()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $contents[] = VanillaItems::GOLDEN_HELMET()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                $contents[] = VanillaItems::GOLDEN_CHESTPLATE()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                $contents[] = VanillaItems::GOLDEN_LEGGINGS()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                $contents[] = VanillaItems::GOLDEN_BOOTS()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                $contents[] = VanillaItems::GOLDEN_APPLE()->setCount(15);
                $contents[] = VanillaItems::BOW()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH()))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY()))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 3));
                $contents[] = VanillaItems::ARROW()->setCount(64);
                $contents[] = VanillaItems::POTION()->setType(PotionType::LONG_SWIFTNESS);
                $contents[] = VanillaItems::POTION()->setType(PotionType::LONG_REGENERATION);
                break;

            case $this::KIT_JOUEUR:
                $contents[] = VanillaItems::IRON_SWORD()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS()));
                $contents[] = VanillaItems::IRON_HELMET()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING()))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                $contents[] = VanillaItems::IRON_CHESTPLATE()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING()))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                $contents[] = VanillaItems::IRON_LEGGINGS()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING()))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                $contents[] = VanillaItems::IRON_BOOTS()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING()))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                $contents[] = VanillaItems::BOW();
                $contents[] = VanillaItems::ARROW()->setCount(64);
                $contents[] = VanillaItems::GOLDEN_APPLE()->setCount(10);
                break;

            case $this::KIT_ANGES:
            case $this::KIT_DIABLOTINS:
                $contents[] = VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2));
                $contents[] = VanillaItems::DIAMOND_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()));
                $contents[] = VanillaItems::DIAMOND_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()));
                $contents[] = VanillaItems::DIAMOND_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()));
                $contents[] = VanillaItems::DIAMOND_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION()));
                $contents[] = VanillaItems::BOW()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING()))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH()))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER()));
                $contents[] = VanillaItems::ARROW()->setCount(16);
                $contents[] = VanillaItems::GOLDEN_APPLE()->setCount(10);
                $contents[] = VanillaItems::ENCHANTED_GOLDEN_APPLE();
                $contents[] = VanillaItems::POTION()->setType(PotionType::SWIFTNESS);
                $contents[] = VanillaItems::POTION()->setType(PotionType::SWIFTNESS);
                break;

            case $this::KIT_ARCHANGES:
            case $this::KIT_PERSEPHONE:
                $contents[] = VanillaItems::DIAMOND_SWORD()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING()));
                $contents[] = VanillaItems::DIAMOND_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                $contents[] = VanillaItems::DIAMOND_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                $contents[] = VanillaItems::DIAMOND_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                $contents[] = VanillaItems::DIAMOND_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                $contents[] = VanillaItems::BOW()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 3));
                $contents[] = VanillaItems::ARROW()->setCount(32);
                $contents[] = VanillaItems::GOLDEN_APPLE()->setCount(20);
                $contents[] = VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(2);
                $contents[] = VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HARMING);
                $contents[] = VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HARMING);
                break;

            case $this::KIT_POSEIDON:
            case $this::KIT_HECATE:
                $contents[] = VanillaItems::DIAMOND_SWORD()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2));
                $contents[] = VanillaItems::DIAMOND_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3));
                $contents[] = VanillaItems::DIAMOND_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3));
                $contents[] = VanillaItems::DIAMOND_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3));
                $contents[] = VanillaItems::DIAMOND_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3));
                $contents[] = VanillaItems::BOW()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 3));
                $contents[] = VanillaItems::ARROW()->setCount(64);
                $contents[] = VanillaItems::FISHING_ROD();
                $contents[] = VanillaItems::GOLDEN_APPLE()->setCount(32);
                $contents[] = VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(3);
                $contents[] = VanillaItems::POTION()->setType(PotionType::SWIFTNESS);
                $contents[] = VanillaItems::POTION()->setType(PotionType::FIRE_RESISTANCE);
                break;

            case $this::KIT_ZEUS:
            case $this::KIT_HADES:
                $contents[] = VanillaItems::DIAMOND_SWORD()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2));
                $contents[] = VanillaItems::DIAMOND_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                $contents[] = VanillaItems::DIAMOND_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                $contents[] = VanillaItems::DIAMOND_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                $contents[] = VanillaItems::DIAMOND_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                $contents[] = VanillaItems::BOW()
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH(), 2))
                    ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 3));
                $contents[] = VanillaItems::ARROW()->setCount(64);
                $contents[] = VanillaItems::ARROW()->setCount(64);
                $contents[] = VanillaItems::FISHING_ROD();
                $contents[] = VanillaItems::GOLDEN_APPLE()->setCount(32);
                $contents[] = VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(4);
                $contents[] = VanillaItems::ENDER_PEARL();
                $contents[] = VanillaItems::POTION()->setType(PotionType::SWIFTNESS);
                $contents[] = VanillaItems::POTION()->setType(PotionType::STRONG_SWIFTNESS);
                $contents[] = VanillaItems::POTION()->setType(PotionType::LONG_FIRE_RESISTANCE);
                break;
        }

        return $contents;
    }

    public function givePlayerKit(Session $player, int $kit, int $count = 1): void
    {
        $contents = $this->getKitContents($kit);
        for($c = 1; $c <= $count; $c++) {
            foreach ($contents as $item) {
                if ($item instanceof Armor && $player->getArmorInventory()->canAddItem($item)) {
                    $player->getArmorInventory()->addItem($item);
                }else {
                    $player->safeGiveItem($item);
                }
            }
        }
    }

    public function getKitCooldown(int $kit): int
    {
        return match ($kit) {
            $this::KIT_JOUEUR => 20,
            $this::KIT_HOURLY => 60*60,
            $this::KIT_ARCHER => 60*60*2,
            $this::KIT_REFILL => 60*60*12,
            $this::KIT_DAILY, $this::KIT_WEEKLY, $this::KIT_ANGES, $this::KIT_DIABLOTINS => 60*60*24,
            $this::KIT_ARCHANGES, $this::KIT_PERSEPHONE => 60*60*36,
            $this::KIT_POSEIDON, $this::KIT_HECATE => 60*60*48,
            $this::KIT_ZEUS, $this::KIT_HADES => 60*60*72,
            default => 0,
        };
    }
}