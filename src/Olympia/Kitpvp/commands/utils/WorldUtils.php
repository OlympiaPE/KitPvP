<?php

namespace Olympia\Kitpvp\commands\utils;

use Exception;
use FilesystemIterator;
use Olympia\Kitpvp\managers\types\Managers;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\Server;
use pocketmine\utils\Binary;
use pocketmine\world\format\io\data\BaseNbtWorldData;
use pocketmine\world\format\io\data\BedrockWorldData;
use pocketmine\world\Position;
use pocketmine\world\World;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;

class WorldUtils
{
    public static function copyWorld(string $from, string $name): string
    {
        $server = Server::getInstance();
        @mkdir($server->getDataPath() . "/worlds/$name/");
        @mkdir($server->getDataPath() . "/worlds/$name/db/");
        copy($server->getDataPath() . "/worlds/" . $from. "/level.dat", $server->getDataPath() . "/worlds/$name/level.dat");
        $oldWorldPath = $server->getDataPath() . "/worlds/$from/level.dat";
        $newWorldPath = $server->getDataPath() . "/worlds/$name/level.dat";

        $oldWorldNbt = new BedrockWorldData($oldWorldPath);
        $newWorldNbt = new BedrockWorldData($newWorldPath);

        $worldData = $oldWorldNbt->getCompoundTag();
        $newWorldNbt->getCompoundTag()->setString("LevelName", $name);


        $nbt = new LittleEndianNbtSerializer();
        $buffer = $nbt->write(new TreeRoot($worldData));
        file_put_contents(Path::join($newWorldPath), Binary::writeLInt(BedrockWorldData::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
        self::copyDir($server->getDataPath() . "/worlds/" . $from . "/db", $server->getDataPath() . "/worlds/$name/db/");

        return $name;
    }

    public static function copyDir($from, $to): void
    {
        $to = rtrim($to, "\\/") . "/";
        /** @var SplFileInfo $file */
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from)) as $file){
            if($file->isFile()){
                $target = $to . ltrim(substr($file->getRealPath(), strlen($from)), "\\/");
                $dir = dirname($target);
                if(!is_dir($dir)){
                    mkdir(dirname($target), 0777, true);
                }
                copy($file->getRealPath(), $target);
            }
        }
    }

    public static function renameWorld(string $oldName, string $newName): void
    {
        $from = Server::getInstance()->getDataPath() . "worlds\\" . $oldName;
        $to = Server::getInstance()->getDataPath() . "worlds\\" . $newName;

        try {
            rename($from, $to);
        } catch(Exception $e) {
            throw new RuntimeException("Unable to rename world \"$oldName\" to \"$newName\": {$e->getMessage()}");
        }

        $newWorld = Server::getInstance()->getWorldManager()->getWorldByName($newName);
        Server::getInstance()->getWorldManager()->loadWorld($newName, true);
        if(!$newWorld instanceof World) {
            return;
        }

        $worldData = $newWorld->getProvider()->getWorldData();
        if(!$worldData instanceof BaseNbtWorldData) {
            return;
        }

        $worldData->getCompoundTag()->setString("LevelName", $newName);

        Server::getInstance()->getWorldManager()->unloadWorld($newWorld);
        Server::getInstance()->getWorldManager()->loadWorld($newName, true);
    }

    public static function removeWorld(string $name): int
    {
        $server = Server::getInstance();
        if($server->getWorldManager()->isWorldLoaded($name)) {
            $world = $server->getWorldManager()->getWorldByName($name);

            if(count($world->getPlayers()) > 0) {

                $spawnInfos = Managers::CONFIG()->get("spawn");
                $x = (int)$spawnInfos["x"];
                $y = (int)$spawnInfos["y"];
                $z = (int)$spawnInfos["z"];
                $spawnWorld = $server->getWorldManager()->getWorldByName($spawnInfos["world"]);
                $position = new Position($x, $y, $z, $spawnWorld);

                foreach($world->getPlayers() as $player) {
                    $player->teleport($position);
                }
            }
            $server->getWorldManager()->unloadWorld($world, true);
        }

        $removedFiles = 1;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($worldPath = $server->getDataPath() . "/worlds/$name", FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        /** @var SplFileInfo $fileInfo */
        foreach($files as $fileInfo) {
            if($filePath = $fileInfo->getRealPath()) {
                if($fileInfo->isFile()) {
                    unlink($filePath);
                } else {
                    rmdir($filePath);
                }
                ++$removedFiles;
            }
        }
        rmdir($worldPath);
        return $removedFiles;
    }

    public static function getWorldByFolderName(string $name): ?World
    {
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            if(strtolower($world->getFolderName()) === strtolower($name)) {
                return $world;
            }
        }
        return null;
    }
}

