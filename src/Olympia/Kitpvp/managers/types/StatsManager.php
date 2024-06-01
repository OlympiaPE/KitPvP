<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\managers\ManageLoader;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;

final class StatsManager extends ManageLoader
{
    use SingletonTrait;

    public const STATS_KILL = "kill";
    public const STATS_DEATH = "death";
    public const STATS_KILLSTREAK = "killstreak";
    public const STATS_NERD = "nerd";

    private array $playersStatsDataCache;

    private array $killLeaderboard;
    private array $deathLeaderboard;
    private array $killstreakLeaderboard;
    private array $nerdLeaderboard;

    public function onInit(): void
    {
        $this->updateDataCache();
        $this->updateLeaderboard();
    }

    public function updateDataCache(): void
    {
        $playersStatsDataCache = [];

        $path = Server::getInstance()->getDataPath() . "/players";
        foreach (scandir($path) as $file) {
            if ($file != '.' && $file != '..') {
                $name = pathinfo($file, PATHINFO_FILENAME);
                if(!is_null($player = Server::getInstance()->getPlayerExact($name))) {
                    /** @var OlympiaPlayer $player */
                    $playersStatsDataCache[$name] = [
                        "death" => $player->getDeath(),
                        "kill" => $player->getKill(),
                        "killstreak" => $player->getKillstreak(),
                        "best-killstreak" => $player->getBestKillstreak(),
                        "playing-time" => $player->getPlayingTime()
                    ];
                }else{
                    $playersStatsDataCache[$name] = $this->getOfflinePlayerStat($name);
                }
            }
        }
        $this->playersStatsDataCache = $playersStatsDataCache;
    }

    public function getOfflinePlayerStat(string $player, string $stat = "all"): int|array
    {
        if(isset($this->playersStatsDataCache[$player])) {
            return $this->playersStatsDataCache[$player];
        }elseif(!is_null($data = Server::getInstance()->getOfflinePlayerData($player))) {
            $data = $data->safeClone();
            $properties = $data->getCompoundTag("properties");
            $stats = $properties->getCompoundTag("statistics");

            $death = $stats->getInt("death", 0);
            $kill = $stats->getInt("kill", 0);
            $killstreak = $stats->getInt("killstreak", 0);
            $best_killstreak = $stats->getInt("best-killstreak", 0);
            $playing_time = (int)$stats->getString("playing-time", "0");

            switch ($stat) {

                case "all":
                    return [
                        "death" => $death,
                        "kill" => $kill,
                        "killstreak" => $killstreak,
                        "best-killstreak" => $best_killstreak,
                        "playing-time" => $playing_time
                    ];

                case "death":
                    return $death;

                case "kill":
                    return $kill;

                case "killstreak":
                    return $killstreak;

                case "best_killstreak":
                case "best-killstreak":
                    return $best_killstreak;

                case "playing_time":
                case "playing-time":
                    return $playing_time;
            }
        }
        return 0;
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