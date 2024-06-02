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

    private DataConnector $database;

    private array $serverDataCache;
    private array $playersDataCache;

    public function onLoad(): void
    {
        $this->setRequireSaveOnDisable(true);

        $loader = Loader::getInstance();
        $mysqlFile = "database/mysql.sql";
        $loader->saveResource($mysqlFile);
        $this->database = libasynql::create($loader, Managers::CONFIG()->get("database"), ["mysql" => $mysqlFile]);

        $this->loadServerDataBase();
    }

    public function loadServerDataBase(): void
    {
        $db = $this->getDataBase();
        $serverName = $this->getServerName();

        $db->executeSelect("loadServer", ["name" => $serverName], function (array $rows) use ($db, $serverName) {

            if (empty($rows)) {

                // CREATE SERVER DATABASE
                $db->executeSelect("createServer", [
                    "name" => $serverName,
                    "ip" => Loader::getInstance()->getServer()->getIp(),
                    "port" => (string)Loader::getInstance()->getServer()->getPort(),
                    "server_data" => "[]",
                    "players_data" => "[]",
                ]);

                $this->serverDataCache = [];
                $this->playersDataCache = [];
            }else{
                $this->serverDataCache = json_decode($rows[0]["server_data"], true);
                $this->playersDataCache = json_decode($rows[0]["players_data"], true);
            }
        });
        $db->waitAll();
    }

    public function save(): void
    {
        $db = $this->getDataBase();
        $serverName = $this->getServerName();

        $db->executeSelect("saveServerData", [
            "name" => $serverName,
            "server_data" => json_encode($this->serverDataCache),
            "players_data" => json_encode($this->playersDataCache)
        ], function (array $rows) use ($db) {

            var_dump($rows);
        });

        $db->waitAll();
    }

    public function savePlayersData(): void
    {
        $db = $this->getDataBase();
        $db->executeSelect("setPlayersData", [
            "name" => $this->getServerName(),
            "players_data" => json_encode($this->playersDataCache)
        ], function (array $rows) {

            var_dump($rows);
        });
    }

    public function getDataBase(): DataConnector
    {
        return $this->database;
    }

    public function getServerName(): string
    {
        return str_contains(Loader::getInstance()->getDescription()->getVersion(), "dev")
            ? $this::SERVER_NAME_DEV
            : $this::SERVER_NAME_STABLE;
    }

    public function hasUuidData(string $uuid): bool
    {
        return isset($this->playersDataCache[$uuid]);
    }

    public function createUuidData(string $uuid): void
    {
        $this->playersDataCache[$uuid] = [
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

        $this->savePlayersData();
    }
}