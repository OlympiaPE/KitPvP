<?php

namespace Olympia\Kitpvp\entities;

use Olympia\Kitpvp\managers\types\CosmeticsManager;
use Olympia\Kitpvp\traits\PropertiesTrait;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\player\Player;
use pocketmine\Server;

class SessionProperties
{
    use PropertiesTrait;

    private Session $player;

    public function __construct(Session $player)
    {
        $this->player = $player;

        if (
            is_null(Server::getInstance()->getOfflinePlayerData($player->getName())) ||
            !($nbt = Server::getInstance()->getOfflinePlayerData($player->getName()))->getCompoundTag('properties') ||
            empty($nbt->getCompoundTag("properties")->getValue())
        ) {
            $this->setBaseProperties($this->getDefaultProperties($player));
        } else {
            /** @var $properties CompoundTag */
            $properties = $nbt->getTag("properties");
            $this->setBaseProperties($this->tagToArray($properties));
        }
    }

    public function save(CompoundTag $tag): CompoundTag
    {
        $tag->setTag("properties", $this->arrayToTag($this->getPropertiesList()));
        return $tag;
    }

    public function getDefaultProperties(Player $player): array
    {
        return [
            "ip" => $player->getNetworkSession()->getIp(),
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

    private function tagToArray(CompoundTag|ListTag $nbt): array
    {
        $nbtsArray = [];
        foreach ($nbt->getValue() as $key => $value) {
            if ($value instanceof CompoundTag) {
                $nbtsArray[$key] = $this->tagToArray($value);
            } elseif ($value instanceof ListTag) {
                $nbtsArray[$key] = [];
                foreach ($value as $tag) {
                    $nbtsArray[$key][] = $this->tagToArray($tag);
                }
            } else {
                $nbtsArray[$key] = $value->getValue();
            }
        }
        return $nbtsArray;
    }

    private function arrayToTag(array $array): Tag
    {
        if(array_keys($array) === range(0, count($array) - 1)) {
            return new ListTag(array_map(fn($value) => $this->getTagType($value), $array));
        }
        $tag = CompoundTag::create();
        foreach($array as $key => $value){
            $tag->setTag($key, $this->getTagType($value));
        }
        return $tag;
    }

    private function getTagType($type): ?Tag
    {
        return match (true) {
            is_array($type) => $this->arrayToTag($type),
            is_bool($type) => new ByteTag($type ? 1 : 0),
            is_float($type) => new FloatTag($type),
            is_int($type) => new IntTag($type),
            is_string($type) => new StringTag($type),
            $type instanceof CompoundTag => $type,
            default => null,
        };
    }
}