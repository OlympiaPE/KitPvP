<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\managers\Manager;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;

final class ScoreboardManager extends Manager
{
    private array $scoreboard = [];

    private array $players = [];

    public function onLoad(): void
    {
    }

    public function getPlayersToDisplay(): array
    {
        return $this->players;
    }

    public function addPlayerToDisplay(Player $player): void
    {
        $this->players[] = $player->getName();
    }

    public function removePlayerToDisplay(Player $player): void
    {
        unset($this->players[array_search($player->getName(), $this->players)]);
    }

    public function getObjectiveName(Player $player): ?string
    {
        return $this->scoreboard[$player->getName()] ?? null;
    }
    
    public function remove(Player $player): void
    {
        $objectiveName = $this->getObjectiveName($player);
        $player->getNetworkSession()->sendDataPacket(RemoveObjectivePacket::create(
            $objectiveName
        ));
        unset($this->scoreboard[$player->getName()]);
    }
    
    public function new(Player $player, string $objectiveName, string $displayName): void
    {
        if(isset($this->scoreboard[$player->getName()])) {

            $this->remove($player);
        }
        $player->getNetworkSession()->sendDataPacket(SetDisplayObjectivePacket::create(
            "sidebar",
            $objectiveName,
            $displayName,
            "dummy",
            0
        ));
        $this->scoreboard[$player->getName()] = $objectiveName;
    }
    
    public function setLine(Player $player, int $score, string $message): void
    {
        if(!isset($this->scoreboard[$player->getName()])) {

            return;
        }
        if($score > 15 || $score < 1) {

            return;
        }
        
        $objectiveName = $this->getObjectiveName($player);
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $objectiveName;
        $entry->type = $entry::TYPE_FAKE_PLAYER;
        $entry->customName = $message;
        $entry->score = $score;
        $entry->scoreboardId = $score;
        
        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_CHANGE;
        $pk->entries[] = $entry;
        
        $player->getNetworkSession()->sendDataPacket($pk);
    }
}