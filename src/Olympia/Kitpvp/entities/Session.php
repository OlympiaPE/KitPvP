<?php

namespace Olympia\Kitpvp\entities;

use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\RankSystem;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\menu\gui\GiveGui;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\player\Player;
use pocketmine\player\PlayerInfo;
use pocketmine\Server;

class Session extends Player
{
    public const DUEL_STATE_NONE = 0;
    public const DUEL_STATE_FIGHTER = 1;
    public const DUEL_STATE_SPECTATOR = 2;

    private SessionProperties $properties;
    private SessionCooldowns $cooldowns;

    private ?GiveGui $giveGui = null;

    private ?int $connectionTime = null;
    private int $duelState;
    private ?int $duelId = null;
    private bool $inTournament = false;

    /**
     * @param Server $server
     * @param NetworkSession $session
     * @param PlayerInfo $playerInfo
     * @param bool $authenticated
     * @param Location $spawnLocation
     * @param CompoundTag|null $namedtag
     */
    public function __construct(Server $server, NetworkSession $session, PlayerInfo $playerInfo, bool $authenticated, Location $spawnLocation, ?CompoundTag $namedtag)
    {
        parent::__construct($server, $session, $playerInfo, $authenticated, $spawnLocation, $namedtag);

        $this->duelState = $this::DUEL_STATE_NONE;

        $this->properties = new SessionProperties($this);
        $this->updateProperties();

        $this->cooldowns = new SessionCooldowns($this);

        $this->updateCosmeticsCategories();
    }

    /**
     * @return CompoundTag
     */
    public function saveNBT(): CompoundTag
    {
        $this->getCooldowns()->saveAllCooldowns();

        $nbt = parent::saveNBT();
        !isset($this->properties) ?: $nbt = $this->properties->save($nbt);

        return $nbt;
    }

    public function setHealth(float $amount): void
    {
        if (isset($this->properties)) {
            $roundedAmount = $amount <= $this->getMaxHealth() ? round($amount, 1) : $this->getMaxHealth();
            $this->setNameTag($this->getDisplayName() . "\n{$roundedAmount}");
        }

        parent::setHealth($amount);
    }

    public function getProperties(): SessionProperties
    {
        return $this->properties;
    }

    private function updateProperties(): void
    {
        $defaultProperties = $this->properties->getDefaultProperties($this);
        $playerProperties = $this->properties->getPropertiesList();

        $less = array_diff(array_keys($defaultProperties), array_keys($playerProperties));
        $tooMuch = array_diff(array_keys($playerProperties), array_keys($defaultProperties));

        if (!empty($less)) {
            foreach ($less as $toAdd) {
                $this->properties->setProperties($toAdd, $defaultProperties[$toAdd]);
            }
        }

        if (!empty($tooMuch)) {
            foreach ($tooMuch as $toRemove) {
                $this->properties->removeProperties($toRemove);
            }
        }
    }

    public function getCooldowns(): SessionCooldowns
    {
        return $this->cooldowns;
    }

    public function getSettings(): array
    {
        return $this->properties->getProperties("settings");
    }

    public function setSettings(array $settings): void
    {
        $this->properties->setProperties("settings", $settings);
    }

    public function getRank(): Rank
    {
        return RankSystem::getInstance()->getSessionManager()->get($this)?->getHighestRank() ?? RankSystem::getInstance()->getRankManager()->getDefault();
    }

    public function getRankName(): string
    {
        return $this->getRank()->getName();
    }

    private function updateCosmeticsCategories(): void
    {
        $playerCosmetics = $this->getAllCosmetics();

        // MISSING CATEGORIES
        $categories = Managers::COSMETICS()->getCategoriesName();

        $missingCategories = array_diff($categories, array_keys($playerCosmetics));

        if(!empty($missingCategories)) {

            foreach ($missingCategories as $missingCategory) {

                $playerCosmetics[$missingCategory] = [];
                $this->properties->setNestedProperties("cosmetics.$missingCategory", []);
            }
        }

        // MISSING COSMETICS
        $categoriesCosmetics = Managers::COSMETICS()->getCategories();

        foreach ($categoriesCosmetics as $category => $cosmetics) {

            $playerCategoryCosmetics = $playerCosmetics[$category];
            $missingCosmetics = array_diff($cosmetics, array_keys($playerCategoryCosmetics));

            if(!empty($missingCosmetics)) {

                foreach ($missingCosmetics as $missingCosmetic) {

                    $this->properties->setNestedProperties("cosmetics.$category.$missingCosmetic", 0);
                }
            }
        }
    }

    public function getAllCosmetics(): array
    {
        return $this->properties->getProperties("cosmetics");
    }

    public function addCosmetic(string $category, string $cosmetic): void
    {
        if(!$this->hasCosmeticByCategory($category, $cosmetic)) {

            $this->properties->setNestedProperties("cosmetics.$category.$cosmetic", 1);
        }
    }

    public function removeCosmetic(string $category, string $cosmetic): void
    {
        if(!$this->hasCosmeticByCategory($category, $cosmetic)) {

            $this->properties->setNestedProperties("cosmetics.$category.$cosmetic", 0);
        }
    }

    public function getCosmeticsByCategory(string $category): array
    {
        return $this->getAllCosmetics()[$category];
    }

    public function hasCosmeticByCategory(string $category, string $cosmetic): bool
    {
        return (bool)$this->getCosmeticsByCategory($category)[$cosmetic];
    }

    public function getAllEquippedCosmetics(): array
    {
        return $this->properties->getProperties("equipped-cosmetics");
    }

    public function removeCosmeticEquipped(string $cosmeticType): void
    {
        $this->properties->setNestedProperties("equipped-cosmetics.$cosmeticType", 0);
    }

    public function setCosmeticEquipped(string $cosmeticType, string $category, string $cosmetic): void
    {
        $this->properties->setNestedProperties("equipped-cosmetics.$cosmeticType", ["category" => $category, "cosmetic" => $cosmetic]);
    }

    public function getEquippedCosmetic(string $cosmeticType): bool|array
    {
        return $this->getAllEquippedCosmetics()[$cosmeticType];
    }

    public function hasCosmeticEquipped(string $cosmeticType, string $cosmetic): bool
    {
        return $this->getEquippedCosmetic($cosmeticType) && $this->getEquippedCosmetic($cosmeticType)["cosmetic"] === $cosmetic;
    }

    public function setConnectionTime(): void
    {
        $this->connectionTime = time();
    }

    public function getPlayingTime(): int
    {
        if($this->connectionTime !== null) {
            $time = (int)$this->properties->getNestedProperties("statistics.playing-time");
            return ($time + (time() - $this->connectionTime));
        }else{
            return (int)$this->properties->getNestedProperties("statistics.playing-time");
        }
    }

    public function updatePlayingTime(): void
    {
        if($this->connectionTime !== null) {
            $time = (int)$this->properties->getNestedProperties("statistics.playing-time");
            $time += time() - $this->connectionTime;
            $this->properties->setNestedProperties("statistics.playing-time", (string)$time);
        }
    }

    public function getKill(): int
    {
        return $this->properties->getNestedProperties("statistics.kill");
    }

    public function addKill(): void
    {
        $kill = $this->properties->getNestedProperties("statistics.kill") + 1;
        $this->properties->setNestedProperties("statistics.kill", $kill);
    }

    public function getDeath(): int
    {
        return $this->properties->getNestedProperties("statistics.death");
    }

    public function addDeath(): void
    {
        $death = $this->properties->getNestedProperties("statistics.death") + 1;
        $this->properties->setNestedProperties("statistics.death", $death);
    }

    public function getKillstreak(): int
    {
        return $this->properties->getNestedProperties("statistics.killstreak");
    }

    public function addKillstreak(): void
    {
        $death = $this->properties->getNestedProperties("statistics.killstreak") + 1;
        $this->properties->setNestedProperties("statistics.killstreak", $death);
    }

    public function resetKillstreak(): void
    {
        $this->properties->setNestedProperties("statistics.killstreak", 0);
    }

    public function getBestKillstreak(): int
    {
        return $this->properties->getNestedProperties("statistics.best-killstreak");
    }

    public function setBestKillstreak(int $killstreak): void
    {
        $this->properties->setNestedProperties("statistics.best-killstreak", $killstreak);
    }

    public function getMoney(): int
    {
        return (int)$this->properties->getProperties("money");
    }

    public function addMoney(int $money): void
    {
        $this->properties->setProperties("money", (string)($this->getMoney() + $money));
    }

    public function removeMoney(int $money): void
    {
        $this->properties->setProperties("money", (string)($this->getMoney() - $money));
    }

    public function hasEnoughMoney(int $money): bool
    {
        return $this->getMoney() - $money >= 0;
    }

    public function setDuelState(int $duelState): void
    {
        $this->duelState = $duelState;
    }

    public function getDuelState(): int
    {
        return $this->duelState;
    }

    public function setDuelId(int $id): void
    {
        $this->duelId = $id;
    }

    public function resetDuelId(): void
    {
        $this->duelId = null;
    }

    public function getDuelId(): ?int
    {
        return $this->duelId;
    }

    public function setInTournament(bool $inTournament = true): void
    {
        $this->inTournament = $inTournament;
    }

    public function inTournament(): bool
    {
        return $this->inTournament;
    }

    public function canFight(): bool
    {
        return
            strtolower($this->getWorld()->getDisplayName()) !== strtolower(Managers::CONFIG()->getNested("spawn.world")) ||
            $this->getPosition()->getFloorY() < Managers::CONFIG()->getNested("spawn.safe-height");
    }

    public function resetGiveGui(): void
    {
        $this->giveGui = null;
    }

    public function safeGiveItem(Item $item, int $giveCount = 1): void
    {
        $exceeds = [];

        for ($c = 1; $c <= $giveCount; $c++) {
            if ($this->getInventory()->canAddItem($item)) {
                $this->getInventory()->addItem($item);
            }else{
                $exceeds[] = $item;
            }
        }

        if (!empty($exceeds)) {
            if (is_null($this->giveGui)) {
                $this->giveGui = new GiveGui($exceeds);
                $this->giveGui->send($this);
            }else{
                $this->giveGui->addItems($exceeds);
            }
        }
    }
}