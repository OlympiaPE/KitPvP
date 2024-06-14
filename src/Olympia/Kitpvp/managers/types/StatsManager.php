<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\managers\Manager;
use Olympia\Kitpvp\managers\Managers;

final class StatsManager extends Manager
{
    public const STATS_KILL = "kill";
    public const STATS_DEATH = "death";
    public const STATS_KILLSTREAK = "killstreak";
    public const STATS_NERD = "nerd";

    private array $playersStatsDataCache;

    private array $killLeaderboard;
    private array $deathLeaderboard;
    private array $killstreakLeaderboard;
    private array $nerdLeaderboard;

    public function onLoad(): void
    {
        $this->updateDataCache();
        $this->updateLeaderboard();
    }

    public function getPlayerStat(string $username, string $stat = "all"): array
    {
        return match ($stat) {
            "all" => $this->playersStatsDataCache[$username] ?? [],
            "death" => $this->playersStatsDataCache[$username]["death"] ?? [],
            "kill" => $this->playersStatsDataCache[$username]["kill"] ?? [],
            "killstreak" => $this->playersStatsDataCache[$username]["killstreak"] ?? [],
            "best-killstreak" => $this->playersStatsDataCache[$username]["best-killstreak"] ?? [],
            "playing-time" => $this->playersStatsDataCache[$username]["playing-time"] ?? [],
            default => [],
        };
    }

    public function updateDataCache(): void
    {
        $playersStatsDataCache = [];

        foreach (Managers::DATABASE()->getAllPlayersData() as $data) {
            $username = $data["username"] ?? uniqid("Unknown-");
            $statsData = $data["statistics"];
            $playersStatsDataCache[$username] = [
                "death" => $statsData["death"],
                "kill" => $statsData["kill"],
                "killstreak" => $statsData["killstreak"],
                "best-killstreak" => $statsData["best-killstreak"],
                "playing-time" => $statsData['playing-time']
            ];

        }
        $this->playersStatsDataCache = $playersStatsDataCache;
    }

    public function updateLeaderboard(): void
    {
        $killLeaderboard = [];
        $deathLeaderboard = [];
        $killstreakLeaderboard = [];
        $nerdLeaderboard = [];

        foreach($this->playersStatsDataCache as $player => $stats) {
            $killLeaderboard[$player] = $stats["kill"];
            $deathLeaderboard[$player] = $stats["death"];
            $killstreakLeaderboard[$player] = $stats["best-killstreak"];
            $nerdLeaderboard[$player] = $stats["playing-time"];
        }

        $removeKeys = function (array &$array) {
            $keysToRemove = array_slice($array, 10, null, true);
            foreach ($keysToRemove as $key => $value) {
                unset($array[$key]);
            }
        };

        arsort($killLeaderboard);
        $removeKeys($killLeaderboard);

        arsort($deathLeaderboard);
        $removeKeys($deathLeaderboard);

        arsort($killstreakLeaderboard);
        $removeKeys($killstreakLeaderboard);

        arsort($nerdLeaderboard);
        $removeKeys($nerdLeaderboard);

        $this->killLeaderboard = $killLeaderboard;
        $this->deathLeaderboard = $deathLeaderboard;
        $this->killstreakLeaderboard = $killstreakLeaderboard;
        $this->nerdLeaderboard = $nerdLeaderboard;
    }

    public function getLeaderboard(string $name): ?array
    {
        return match ($name) {
            $this::STATS_KILL => $this->killLeaderboard,
            $this::STATS_DEATH => $this->deathLeaderboard,
            $this::STATS_KILLSTREAK => $this->killstreakLeaderboard,
            $this::STATS_NERD => $this->nerdLeaderboard,
            default => null
        };
    }
}