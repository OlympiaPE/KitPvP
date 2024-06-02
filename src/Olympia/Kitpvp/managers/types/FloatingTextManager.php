<?php

namespace Olympia\Kitpvp\managers\types;

use Closure;
use Olympia\Kitpvp\entities\objects\FloatingText;
use Olympia\Kitpvp\managers\Manager;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\Utils;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;

final class FloatingTextManager extends Manager
{
    /** @var FloatingText[] $floatingText */
    private array $floatingText = [];

    private string $world;

    public function onLoad(): void
    {
        $this->world = Managers::CONFIG()->getNested("spawn.world");
        $this->spawnAllFloatingTexts();
    }

    public function onDisable(): void
    {
        $this->deleteAllFloatingTexts();
    }

    public function spawnAllFloatingTexts(): void
    {
        $config = Managers::CONFIG()->get("floating-text");
        $posTopMoney = $config["top-money"];
        $posTopKill = $config["top-kill"];
        $posTopDeath = $config["top-death"];
        $posTopKillstreak = $config["top-killstreak"];
        $posTopNerd = $config["top-nerd"];

        $this->createFloatingText(
            $this->getLocationByCoordinates($posTopMoney["x"], $posTopMoney["y"], $posTopMoney["z"]),
            Managers::CONFIG()->getNested("leaderboards.money.title"),
            function (FloatingText $entity) {

                /** @var MoneyManager $this */
                $moneyData = $this->getPlayersMoneyData();
                arsort($moneyData);

                $nametag = Managers::CONFIG()->getNested("leaderboards.money.title");
                $top = 1;
                foreach ($moneyData as $player => $money) {

                    if($top > 10) break;

                    $nametag .= "\n" . str_replace(
                        ["{top}", "{player}", "{money}"],
                        [$top, $player, $money],
                        Managers::CONFIG()->getNested("leaderboards.money.line")
                    );
                    $top++;
                }
                $entity->setNameTag($nametag);
            },
            20*60,
            Managers::MONEY()
        );

        $this->createFloatingText(
            $this->getLocationByCoordinates($posTopKill["x"], $posTopKill["y"], $posTopKill["z"]),
            Managers::CONFIG()->getNested("leaderboards.kill.title"),
            function (FloatingText $entity) {

                /** @var StatsManager $this */
                $killLeaderboard = $this->getLeaderboard($this::STATS_KILL);

                $nametag = Managers::CONFIG()->getNested("leaderboards.kill.title");
                $top = 1;
                foreach ($killLeaderboard as $player => $kill) {
                    $nametag .= "\n" . str_replace(
                        ["{top}", "{player}", "{kill}"],
                        [$top, $player, $kill],
                        Managers::CONFIG()->getNested("leaderboards.kill.line")
                    );
                    $top++;
                }
                $entity->setNameTag($nametag);
            },
            20*60,
            Managers::STATS()
        );

        $this->createFloatingText(
            $this->getLocationByCoordinates($posTopDeath["x"], $posTopDeath["y"], $posTopDeath["z"]),
            Managers::CONFIG()->getNested("leaderboards.death.title"),
            function (FloatingText $entity) {

                /** @var StatsManager $this */
                $deathLeaderboard = $this->getLeaderboard($this::STATS_DEATH);

                $nametag = Managers::CONFIG()->getNested("leaderboards.death.title");
                $top = 1;
                foreach ($deathLeaderboard as $player => $death) {
                    $nametag .= "\n" . str_replace(
                        ["{top}", "{player}", "{death}"],
                        [$top, $player, $death],
                        Managers::CONFIG()->getNested("leaderboards.death.line")
                    );
                    $top++;
                }
                $entity->setNameTag($nametag);
            },
            20*60,
            Managers::STATS()
        );

        $this->createFloatingText(
            $this->getLocationByCoordinates($posTopKillstreak["x"], $posTopKillstreak["y"], $posTopKillstreak["z"]),
            Managers::CONFIG()->getNested("leaderboards.killstreak.title"),
            function (FloatingText $entity) {

                /** @var StatsManager $this */
                $killstreakLeaderboard = $this->getLeaderboard($this::STATS_KILLSTREAK);

                $nametag = Managers::CONFIG()->getNested("leaderboards.killstreak.title");
                $top = 1;
                foreach ($killstreakLeaderboard as $player => $killstreak) {
                    $nametag .= "\n" . str_replace(
                        ["{top}", "{player}", "{killstreak}"],
                        [$top, $player, $killstreak],
                        Managers::CONFIG()->getNested("leaderboards.killstreak.line")
                    );
                    $top++;
                }
                $entity->setNameTag($nametag);
            },
            20*60,
            Managers::STATS()
        );

        $this->createFloatingText(
            $this->getLocationByCoordinates($posTopNerd["x"], $posTopNerd["y"], $posTopNerd["z"]),
            Managers::CONFIG()->getNested("leaderboards.nerd.title"),
            function (FloatingText $entity) {

                /** @var StatsManager $this */
                $nerdLeaderboard = $this->getLeaderboard($this::STATS_NERD);

                $nametag = Managers::CONFIG()->getNested("leaderboards.nerd.title");
                $top = 1;
                foreach ($nerdLeaderboard as $player => $nerd) {
                    $nametag .= "\n" . str_replace(
                        ["{top}", "{player}", "{nerd}"],
                        [$top, $player, Utils::durationToShortString($nerd)],
                        Managers::CONFIG()->getNested("leaderboards.nerd.line")
                    );
                    $top++;
                }
                $entity->setNameTag($nametag);
            },
            20*60,
            Managers::STATS()
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