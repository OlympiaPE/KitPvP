<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\managers\Manager;
use Olympia\Kitpvp\tournament\Tournament;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;

final class TournamentManager extends Manager
{
    public const TOURNAMENT_TYPE_NODEBUFF = "nodebuff";
    public const TOURNAMENT_TYPE_SUMO = "sumo";
    public const TOURNAMENT_TYPE_BRACKET = "bracket";

    private ?Tournament $tournament = null;

    public function onLoad(): void
    {
    }

    public function hasCurrentTournament(): bool
    {
        return !is_null($this->tournament);
    }

    public function createTournament(Session $hoster, string $type): void
    {
        $this->tournament = new Tournament($hoster->getName(), $type);
    }

    public function removeTournament(): void
    {
        $this->tournament = null;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function isTournamentStarted(): bool
    {
        return (bool)$this->getTournament()?->isStarted();
    }

    public function givePlayerTournamentKit(Session $player, string $type): void
    {
        switch ($type) {

            case $this::TOURNAMENT_TYPE_NODEBUFF:

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

                for ($s = 27; $s <= 35; $s++) {
                    if ($s === 35) {
                        $contents[$s] = VanillaItems::POTION()->setType(PotionType::STRONG_SWIFTNESS);
                    }else{
                        $contents[$s] = VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING);
                    }
                }

                $player->getArmorInventory()->setContents($armor);
                $player->getInventory()->setContents($contents);
                break;

            case $this::TOURNAMENT_TYPE_BRACKET:

                $armor = [
                    VanillaItems::IRON_HELMET(),
                    VanillaItems::IRON_CHESTPLATE(),
                    VanillaItems::IRON_LEGGINGS(),
                    VanillaItems::IRON_BOOTS(),
                ];

                /** @var Armor $item */
                foreach ($armor as $item) {
                    $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                }

                $contents = [
                    VanillaItems::IRON_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS())),
                    VanillaItems::FISHING_ROD(),
                    VanillaItems::BOW()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 2)),
                    VanillaItems::GOLDEN_APPLE()->setCount(5),
                    VanillaItems::ARROW()->setCount(10),
                ];

                $player->getArmorInventory()->setContents($armor);
                $player->getInventory()->setContents($contents);
                break;
        }
    }
}