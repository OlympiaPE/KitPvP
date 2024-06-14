<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\libraries\poggit\libasynql\DataConnector;
use Olympia\Kitpvp\libraries\poggit\libasynql\libasynql;
use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Manager;
use Olympia\Kitpvp\managers\Managers;

class DatabaseManager extends Manager
{
    PUBLIC CONST SERVER_NAME_STABLE = "kitpvp";
    PUBLIC CONST SERVER_NAME_DEV = "kitpvp_dev";

    public const PREFIX = "§c[DataBase]";

    private DataConnector $database;

    private array $serverDataCache;
    private array $nestedServerDataCache;

    private array $playersDataCache;
    private array $nestedPlayersDataCache;

    private array $uuidUsernames;

    /**
     * @return void
     */
    public function onLoad(): void
    {
        $this->setRequireSaveOnDisable(true);

        $loader = Loader::getInstance();
        $mysqlFile = "database/mysql.sql";
        $loader->saveResource($mysqlFile);
        $this->database = libasynql::create($loader, Managers::CONFIG()->get("database"), ["mysql" => $mysqlFile]);

        $this->loadServerDataBase();
    }

    /**
     * @return void
     */
    public function loadServerDataBase(): void
    {
        $db = $this->getDataBase();
        $serverName = $this->getServerName();

        $db->executeSelect("loadServer", ["name" => $serverName], function (array $rows) use ($db, $serverName) {

            if (empty($rows)) {

                // CREATE SERVER DATABASE
                $db->executeInsert("createServer", [
                    "name" => $serverName,
                    "ip" => Loader::getInstance()->getServer()->getIp(),
                    "port" => (string)Loader::getInstance()->getServer()->getPort(),
                    "server_data" => json_encode($this->getDefaultServerData()),
                ], function(int $insertId, int $affectedRows) use ($serverName) {
                    if ($affectedRows) {

                        Loader::getInstance()->getLogger()->info($this::PREFIX . " The $serverName server has been added to the database.");

                        $this->serverDataCache = $this->getDefaultServerData();
                        $this->playersDataCache = [];
                    }else{

                        Loader::getInstance()->getLogger()->error($this::PREFIX . " Unable to add $serverName server to database. Server stopped.");
                        Loader::getInstance()->getServer()->shutdown();
                    }
                });
            }else{
                // Update data if values have been added to or removed from default data

                // Server
                $serverData = json_decode($rows[0]["server_data"], true);

                $defaultServerData = $this->getDefaultServerData();

                $serverLess = array_diff(array_keys($defaultServerData), array_keys($serverData));
                $serverTooMuch = array_diff(array_keys($serverData), array_keys($defaultServerData));

                if (!empty($serverLess)) {
                    foreach ($serverLess as $toAdd) {
                        $serverData[$toAdd] = $defaultServerData[$toAdd];
                    }
                }

                // Warning: this code can permanently delete data from the server.
                if (!empty($serverTooMuch)) {
                    foreach ($serverTooMuch as $toRemove) {
                        unset($serverData[$toRemove]);
                    }
                }

                $this->serverDataCache = $serverData;


                // Players
                $playersData = json_decode($rows[0]["players_data"], true);
                foreach ($playersData as $uuid => &$playerData) {

                    $defaultPlayerData = $this->getDefaultPlayerData($playersData["username"] ?? "Unknown");

                    $playerLess = array_diff(array_keys($defaultPlayerData), array_keys($playerData));
                    $playerTooMuch = array_diff(array_keys($playerData), array_keys($defaultPlayerData));

                    if (!empty($playerLess)) {
                        foreach ($playerLess as $toAdd) {
                            $playerData[$toAdd] = $defaultPlayerData[$toAdd];
                        }
                    }

                    // Warning: this code can permanently remove data from the player.
                    if (!empty($playerTooMuch)) {
                        foreach ($playerTooMuch as $toRemove) {
                            unset($playerData[$toRemove]);
                        }
                    }

                    $this->uuidUsernames[$uuid] = strtolower($playerData["username"]);
                }
                unset($playerData);
                $this->playersDataCache = $playersData;
            }
        });
        $db->waitAll();
    }

    /**
     * @param bool $wait
     * @return void
     */
    public function save(bool $wait = true): void
    {
        $db = $this->getDataBase();
        $serverName = $this->getServerName();

        $db->executeChange("saveServerData", [
            "name" => $serverName,
            "server_data" => json_encode($this->serverDataCache),
            "players_data" => json_encode($this->playersDataCache)
        ], function (int $affectedRows) {
            if (!$affectedRows) {
                Loader::getInstance()->getLogger()->error($this::PREFIX . " No changes have been made to the database.");
            }
        });

        if ($wait) $db->waitAll();
    }

    /**
     * @return DataConnector
     */
    public function getDataBase(): DataConnector
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return str_contains(Loader::getInstance()->getDescription()->getVersion(), "dev")
            ? $this::SERVER_NAME_DEV
            : $this::SERVER_NAME_STABLE;
    }

    /**
     * @return int
     */
    public function saveServerData(): int
    {
        $result = 0;

        $this->getDataBase()->executeChange("setServerData", [
            "name" => $this->getServerName(),
            "server_data" => json_encode($this->serverDataCache)
        ], function (int $affectedRows) use (&$result) {
            $result = $affectedRows;
        });

        return $result;
    }

    /**
     * @return array[]
     */
    public function getDefaultServerData(): array
    {
        return [
            "koth" => [
                "started" => false,
                "last-capture-time" => null,
            ],
            "hdv" => [],
        ];
    }

    /**
     * @return array
     */
    public function getAllServerData(): array
    {
        return $this->serverDataCache ?? [];
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getServerData(string $key, mixed $default = false): mixed
    {
        return $this->serverDataCache[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getNestedServerData(string $key, mixed $default = false): mixed
    {
        if(isset($this->nestedServerDataCache[$key])) {
            return $this->nestedServerDataCache[$key];
        }

        $vars = explode(".", $key);
        $base = array_shift($vars);
        if(isset($this->serverDataCache[$base])) {
            $base = $this->serverDataCache[$base];
        }else{
            return $default;
        }

        while(count($vars) > 0) {
            $baseKey = array_shift($vars);
            if(is_array($base) && isset($base[$baseKey])) {
                $base = $base[$baseKey];
            }else{
                return $default;
            }
        }

        return $this->nestedServerDataCache[$key] = $base;
    }

    /**
     * @param array $data
     * @return void
     */
    public function setAllServerData(array $data): void
    {
        $this->serverDataCache = $data;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setServerData(string $key, mixed $value): void
    {
        $this->serverDataCache[$key] = $value;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setNestedServerData(string $key, mixed $value): void
    {
        $vars = explode(".", $key);
        $base = array_shift($vars);

        if(!isset($this->serverDataCache[$base])){
            $this->serverDataCache[$base] = [];
        }

        $base = &$this->serverDataCache[$base];

        while(count($vars) > 0){
            $baseKey = array_shift($vars);
            if(!isset($base[$baseKey])){
                $base[$baseKey] = [];
            }
            $base = &$base[$baseKey];
        }

        $base = $value;
        $this->nestedServerDataCache = [];
    }

    /**
     * @return int
     */
    public function savePlayersData(): int
    {
        $result = 0;

        $this->getDataBase()->executeChange("setPlayersData", [
            "name" => $this->getServerName(),
            "players_data" => json_encode($this->playersDataCache)
        ], function (int $affectedRows) use (&$result) {
            $result = $affectedRows;
        });

        return $result;
    }

    /**
     * @param string $username
     * @return array
     */
    public function getDefaultPlayerData(string $username): array
    {
        return [
            "username" => $username,
            "reclaim" => false,
            "money" => "0",
            "cooldowns" => [],
            "cosmetics" => [],
            "equipped-cosmetics" => [
                CosmeticsManager::COSMETIC_COSTUME => 0,
                CosmeticsManager::COSMETIC_CAPE => 0
            ],
            "settings" => [
                'kill-message' => true,
                'cps' => true,
                'scoreboard' => true,
            ],
            "statistics" => [
                'death' => 0,
                'kill' => 0,
                'killstreak' => 0,
                'best-killstreak' => 0,
                'playing-time' => "0",
            ]
        ];
    }

    /**
     * @return array
     */
    public function getAllPlayersData(): array
    {
        return $this->playersDataCache;
    }

    /**
     * @param string $key
     * @param bool $keyUsername
     * @return array
     */
    public function getPlayersDataByKey(string $key, bool $keyUsername = false): array
    {
        $playersData = $this->getAllPlayersData();
        $array = [];
        $isNested = str_contains($key, '.');

        foreach ($playersData as $uuid => $data) {
            $array[($keyUsername && isset($data["username"])) ? $data["username"] : $uuid] = $isNested ? $this->getNestedUuidData($uuid, $key) : $this->getUuidData($uuid, $key);
        }

        return $array;
    }

    /**
     * @param string $uuid
     * @return bool
     */
    public function hasUuidData(string $uuid): bool
    {
        return isset($this->playersDataCache[$uuid]);
    }

    /**
     * @param string $uuid
     * @param string $username
     * @return void
     */
    public function createUuidData(string $uuid, string $username): void
    {
        $this->uuidUsernames[$uuid] = strtolower($username);
        $this->playersDataCache[$uuid] = $this->getDefaultPlayerData($username);
        $this->savePlayersData();
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function getAllUuidData(string $uuid): array
    {
        return $this->playersDataCache[$uuid] ?? [];
    }

    /**
     * @param string $uuid
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getUuidData(string $uuid, string $key, mixed $default = false): mixed
    {
        return $this->playersDataCache[$uuid][$key] ?? $default;
    }

    /**
     * @param string $uuid
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getNestedUuidData(string $uuid, string $key, mixed $default = false): mixed
    {
        if(isset($this->nestedPlayersDataCache[$uuid][$key])) {
            return $this->nestedPlayersDataCache[$uuid][$key];
        }

        $vars = explode(".", $key);
        $base = array_shift($vars);
        if(isset($this->playersDataCache[$uuid][$base])) {
            $base = $this->playersDataCache[$uuid][$base];
        }else{
            return $default;
        }

        while(count($vars) > 0) {
            $baseKey = array_shift($vars);
            if(is_array($base) && isset($base[$baseKey])) {
                $base = $base[$baseKey];
            }else{
                return $default;
            }
        }

        return $this->nestedPlayersDataCache[$uuid][$key] = $base;
    }

    /**
     * @param string $uuid
     * @param array $data
     * @return void
     */
    public function setAllUuidData(string $uuid, array $data): void
    {
        $this->playersDataCache[$uuid] = $data;
    }

    /**
     * @param string $uuid
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setUuidData(string $uuid, string $key, mixed $value): void
    {
        $this->playersDataCache[$uuid][$key] = $value;
    }

    /**
     * @param string $uuid
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setNestedUuidData(string $uuid, string $key, mixed $value): void
    {
        $vars = explode(".", $key);
        $base = array_shift($vars);

        if(!isset($this->playersDataCache[$uuid][$base])){
            $this->playersDataCache[$uuid][$base] = [];
        }

        $base = &$this->playersDataCache[$uuid][$base];

        while(count($vars) > 0){
            $baseKey = array_shift($vars);
            if(!isset($base[$baseKey])){
                $base[$baseKey] = [];
            }
            $base = &$base[$baseKey];
        }

        $base = $value;
        $this->nestedPlayersDataCache[$uuid] = [];
    }

    /**
     * @param string $username
     * @return string|null
     */
    public function getUuidByUsername(string $username): ?string
    {
        return $this->hasUsernameData($username) ? $this->uuidUsernames[array_search($username, $this->uuidUsernames)] : null;
    }

    /**
     * @param string $uuid
     * @return string|null
     */
    public function getUsernameByUuid(string $uuid): ?string
    {
        return $this->uuidUsernames[$uuid] ?? null;
    }

    /**
     * @param string $username
     * @return bool
     */
    public function hasUsernameData(string $username): bool
    {
        return in_array($username, $this->uuidUsernames);
    }
}