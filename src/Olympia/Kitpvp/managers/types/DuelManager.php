<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\duel\Duel;
use Olympia\Kitpvp\duel\DuelStates;
use Olympia\Kitpvp\managers\ManageLoader;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\utils\SingletonTrait;

final class DuelManager extends ManageLoader
{
    use SingletonTrait;

    public const DUEL_TYPE_NODEBUFF = 0;
    public const DUEL_TYPE_SUMO = 1;
    public const DUEL_TYPE_ARCHER = 2;
    public const DUEL_TYPE_INVADED = 3;
    public const DUEL_TYPE_KIT_JOUEUR = 4;
    public const DUEL_TYPE_KIT_ANGES = 5;
    public const DUEL_TYPE_KIT_ARCHANGES = 6;
    public const DUEL_TYPE_KIT_POSEIDON = 7;
    public const DUEL_TYPE_KIT_ZEUS = 8;

    /** @var Duel[] */
    private array $duels = [];

    public function onInit(): void
    {
    }

    public function onDisable(): void
    {
        foreach ($this->getDuels() as $duel) {
            $duel->end(true);
        }
        parent::onDisable();
    }

    public function getAllDuelTypes(): array
    {
        return [
            $this::DUEL_TYPE_NODEBUFF => "Nodebuff",
            $this::DUEL_TYPE_SUMO => "Sumo",
            $this::DUEL_TYPE_ARCHER => "Archer",
            $this::DUEL_TYPE_INVADED => "Invaded",
            $this::DUEL_TYPE_KIT_JOUEUR => "Kit Joueur",
            $this::DUEL_TYPE_KIT_ANGES => "Kit Anges",
            $this::DUEL_TYPE_KIT_ARCHANGES => "Kit Archanges",
            $this::DUEL_TYPE_KIT_POSEIDON => "Kit Poséidon",
            $this::DUEL_TYPE_KIT_ZEUS => "Kit Zeus",
        ];
    }

    public function getDuels(): array
    {
        return $this->duels;
    }

    /**
     * @param array $states
     * @return Duel[]
     */
    public function getDuelsByStates(array $states): array
    {
        $duels = [];
        foreach ($this->getDuels() as $duel) {
            if (in_array($duel->getState(), $states)) {
                $duels[] = $duel;
            }
        }
        return $duels;
    }

    public function getDuelTypeDisplayName(int $duelType): string
    {
        return $this->getAllDuelTypes()[$duelType] ?? "Inconnu";
    }

    public function createDuel(OlympiaPlayer $player, OlympiaPlayer $target, int $mise, int $type): void
    {
        $this->duels[] = new Duel(hexdec(uniqid()), $player, $target, $mise, $type);
    }

    public function getDuelIndexById(int $id): ?int
    {
        foreach ($this->duels as $index => $duel) {
            if ($duel->getId() === $id) {
                return $index;
            }
        }
        return null;
    }

    public function getDuelById(int $id): ?Duel
    {
        return $this->getDuels()[$this->getDuelIndexById($id)];
    }

    public function deleteDuel(int $index): void
    {
        unset($this->duels[$index]);
    }

    /**
     * @param OlympiaPlayer $player
     * @return Duel[]
     */
    public function getPlayerDuels(OlympiaPlayer $player): array
    {
        $duels = [];
        foreach ($this->duels as $duel) {
            if (in_array($player->getName(), $duel->getPlayersName())) {
                $duels[] = $duel;
            }
        }
        return $duels;
    }

    public function givePlayerDuelKit(OlympiaPlayer $player, int $type): void
    {
        switch ($type) {

            case $this::DUEL_TYPE_NODEBUFF:

                $armor = [
                    VanillaItems::DIAMOND_HELMET(),
                    VanillaItems::DIAMOND_CHESTPLATE(),
                    VanillaItems::DIAMOND_LEGGINGS(),
                    VanillaItems::DIAMOND_BOOTS(),
                ];

                /** @var Armor $item */
                foreach ($armor as $item) {
                    $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                    $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                }

                $contents = [
                    VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3)),
                    VanillaItems::ENDER_PEARL()->setCount(16),
                    VanillaItems::POTION()->setType(PotionType::STRONG_SWIFTNESS)
                ];

                for ($s = 3; $s <= 35; $s++) {
                    if ($s === 17 || $s === 26 || $s === 35) {
                        $contents[$s] = VanillaItems::POTION()->setType(PotionType::STRONG_SWIFTNESS);
                    }else{
                        $contents[$s] = VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING);
                    }
                }

                $player->getArmorInventory()->setContents($armor);
                $player->getInventory()->setContents($contents);
                break;

            case $this::DUEL_TYPE_ARCHER:

                $armor = [
                    VanillaItems::CHAINMAIL_HELMET(),
                    VanillaItems::CHAINMAIL_CHESTPLATE(),
                    VanillaItems::CHAINMAIL_LEGGINGS(),
                    VanillaItems::CHAINMAIL_BOOTS(),
                ];

                /** @var Armor $item */
                foreach ($armor as $item) {
                    $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                }

                $contents = [
                    VanillaItems::BOW()
                        ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 2))
                        ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY())),
                    VanillaBlocks::OAK_PLANKS()->asItem()->setCount(64),
                    VanillaItems::GOLDEN_APPLE()->setCount(2),
                    VanillaItems::ARROW()
                ];

                $player->getArmorInventory()->setContents($armor);
                $player->getInventory()->setContents($contents);
                break;

            case $this::DUEL_TYPE_INVADED:

                $armor = [
                    VanillaItems::DIAMOND_HELMET(),
                    VanillaItems::DIAMOND_CHESTPLATE(),
                    VanillaItems::DIAMOND_LEGGINGS(),
                    VanillaItems::DIAMOND_BOOTS(),
                ];

                /** @var Armor $item */
                foreach ($armor as $item) {
                    $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
                    $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                }

                $contents = [
                    VanillaItems::DIAMOND_SWORD()
                        ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3))
                        ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2)),
                    VanillaItems::FISHING_ROD(),
                    VanillaItems::BOW()
                        ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 3))
                        ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH(), 2))
                        ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY())),
                    VanillaItems::GOLDEN_APPLE()->setCount(64),
                    VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(2),
                    VanillaItems::POTION()->setType(PotionType::LONG_SWIFTNESS),
                    VanillaItems::SPLASH_POTION()->setType(PotionType::HARMING()),
                    VanillaItems::ENDER_PEARL(),
                    VanillaItems::ARROW(),
                ];

                $player->getArmorInventory()->setContents($armor);
                $player->getInventory()->setContents($contents);
                break;

            case $this::DUEL_TYPE_KIT_JOUEUR:
                KitsManager::getInstance()->givePlayerKit($player, KitsManager::KIT_JOUEUR);
                break;

            case $this::DUEL_TYPE_KIT_ANGES:
                KitsManager::getInstance()->givePlayerKit($player, KitsManager::KIT_ANGES);
                break;

            case $this::DUEL_TYPE_KIT_ARCHANGES:
                KitsManager::getInstance()->givePlayerKit($player, KitsManager::KIT_ARCHANGES);
                break;

            case $this::DUEL_TYPE_KIT_POSEIDON:
                KitsManager::getInstance()->givePlayerKit($player, KitsManager::KIT_POSEIDON);
                break;

            case $this::DUEL_TYPE_KIT_ZEUS:
                KitsManager::getInstance()->givePlayerKit($player, KitsManager::KIT_ZEUS);
                break;
        }
    }
}