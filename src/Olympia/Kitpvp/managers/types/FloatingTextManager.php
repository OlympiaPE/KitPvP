<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\entities\objects\FloatingText;
use Olympia\Kitpvp\managers\ManageLoader;
use Olympia\Kitpvp\utils\Utils;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use Closure;

final class FloatingTextManager extends ManageLoader
{
    use SingletonTrait;

    /** @var FloatingText[] $floatingText */
    private array $floatingText = [];

    private string $world;

    public function onInit(): void
    {
        $this->world = ConfigManager::getInstance()->getNested("spawn.world");
        $this->spawnAllFloatingTexts();
    }

    public function onDisable(): void
    {
        $this->deleteAllFloatingTexts();
        parent::onDisable();
    }

    public function spawnAllFloatingTexts(): void
    {
        $config = ConfigManager::getInstance()->get("floating-text");
        $posTopMoney = $config["top-money"];
        $posTopKill = $config["top-kill"];
        $posTopDeath = $config["top-death"];
        $posTopKillstreak = $config["top-killstreak"];
        $posTopNerd = $config["top-nerd"];

        $this->createFloatingText(
            $this->getLocationByCoordinates($posTopMoney["x"], $posTopMoney["y"], $posTopMoney["z"]),
            ConfigManager::getInstance()->getNested("leaderboards.money.title"),
            function (FloatingText $entity) {

                /** @var MoneyManager $this */
                $moneyData = $this->getPlayersMoneyData();
                arsort($moneyData);

                $nametag = ConfigManager::getInstance()->getNested("leaderboards.money.title");
                $top = 1;
                foreach ($moneyData as $player => $money) {

                    if($top > 10) break;

                    $nametag .= "\n" . str_replace(
                        ["{top}", "{player}", "{money}"],
                        [$top, $player, $money],
                        ConfigManager::getInstance()->getNested("leaderboards.money.line")
                    );
                    $top++;
                }
                $entity->setNameTag($nametag);
            },
            20*60,
            MoneyManager::getInstance()
        );

        $this->createFloatingText(
            $this->getLocationByCoordinates($posTopKill["x"], $posTopKill["y"], $posTopKill["z"]),
            ConfigManager::getInstance()->getNested("leaderboards.kill.title"),
            function (FloatingText $entity) {

                /** @var StatsManager $this */
                $killLeaderboard = $this->getLeaderboard($this::STATS_KILL);

                $nametag = ConfigManager::getInstance()->getNested("leaderboards.kill.title");
                $top = 1;
                foreach ($killLeaderboard as $player => $kill) {
                    $nametag .= "\n" . str_replace(
                        ["{top}", "{player}", "{kill}"],
                        [$top, $player, $kill],
                        ConfigManager::getInstance()->getNested("leaderboards.kill.line")
                    );
                    $top++;
                }
                $entity->setNameTag($nametag);
            },
            20*60,
            StatsManager::getInstance()
        );

        $this->createFloatingText(
            $this->getLocationByCoordinates($posTopDeath["x"], $posTopDeath["y"], $posTopDeath["z"]),
            ConfigManager::getInstance()->getNested("leaderboards.death.title"),
            function (FloatingText $entity) {

                /** @var StatsManager $this */
                $deathLeaderboard = $this->getLeaderboard($this::STATS_DEATH);

                $nametag = ConfigManager::getInstance()->getNested("leaderboards.death.title");
                $top = 1;
                foreach ($deathLeaderboard as $player => $death) {
                    $nametag .= "\n" . str_replace(
                        ["{top}", "{player}", "{death}"],
                        [$top, $player, $death],
                        ConfigManager::getInstance()->getNested("leaderboards.death.line")
                    );
                    $top++;
                }
                $entity->setNameTag($nametag);
            },
            20*60,
            StatsManager::getInstance()
        );

        $this->createFloatingText(
            $this->getLocationByCoordinates($posTopKillstreak["x"], $posTopKillstreak["y"], $posTopKillstreak["z"]),
            ConfigManager::getInstance()->getNested("leaderboards.killstreak.title"),
            function (FloatingText $entity) {

                /** @var StatsManager $this */
                $killstreakLeaderboard = $this->getLeaderboard($this::STATS_KILLSTREAK);

                $nametag = ConfigManager::getInstance()->getNested("leaderboards.killstreak.title");
                $top = 1;
                foreach ($killstreakLeaderboard as $player => $killstreak) {
                    $nametag .= "\n" . str_replace(
                        ["{top}", "{player}", "{killstreak}"],
                        [$top, $player, $killstreak],
                        ConfigManager::getInstance()->getNested("leaderboards.killstreak.line")
                    );
                    $top++;
                }
                $entity->setNameTag($nametag);
            },
            20*60,
            StatsManager::getInstance()
        );

        $this->createFloatingText(
            $this->getLocationByCoordinates($posTopNerd["x"], $posTopNerd["y"], $posTopNerd["z"]),
            ConfigManager::getInstance()->getNested("leaderboards.nerd.title"),
            function (FloatingText $entity) {

                /** @var StatsManager $this */
                $nerdLeaderboard = $this->getLeaderboard($this::STATS_NERD);

                $nametag = ConfigManager::getInstance()->getNested("leaderboards.nerd.title");
                $top = 1;
                foreach ($nerdLeaderboard as $player => $nerd) {
                    $nametag .= "\n" . str_replace(
                        ["{top}", "{player}", "{nerd}"],
                        [$top, $player, Utils::durationToShortString($nerd)],
                        ConfigManager::getInstance()->getNested("leaderboards.nerd.line")
                    );
                    $top++;
                }
                $entity->setNameTag($nametag);
            },
            20*60,
            StatsManager::getInstance()
        );
    }

    public function deleteAllFloatingTexts(): void
    {
        foreach ($this->floatingText as $id => $floatingText) {
            $floatingText->flagForDespawn();
            unset($this->floatingText[$id]);
        }
    }

    public function getLocationByCoordinates($x, $y, $z): Location
    {
        return new Location($x + 0.5, $y + 0.5, $z + 0.5, Server::getInstance()->getWorldManager()->getWorldByName($this->world), 0, 0);
    }

    public function createFloatingText(
        Location $location,
        string $defaultNameTag,
        ?Closure $updateClosure = null,
        int $updateTime = 1,
        mixed $managerClass = null
    ): string
    {
        $id = uniqid();
        $floatingText = new FloatingText($location, CompoundTag::create());
        $floatingText->setNameTag($defaultNameTag);
        $floatingText->setFtId($id);
        $floatingText->setUpdateNameTagClosure($updateClosure);
        $floatingText->setUpdateTime($updateTime);
        $floatingText->setManagerClass($managerClass ?? $floatingText);
        $floatingText->spawnToAll();

        $this->floatingText[$id] = $floatingText;
        return $id;
    }

    public function removeFloatingText(string $id): void
    {
        if (isset($this->floatingText[$id])) {

            $floatingText = $this->floatingText[$id];
            $floatingText->flagForDespawn();
            unset($this->floatingText[$id]);
        }
    }

    public function getFloatingTextById(string $id): ?FloatingText
    {
        return $this->floatingText[$id] ?? null;
    }
}