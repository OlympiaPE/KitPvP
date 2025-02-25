<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\Manager;
use pocketmine\player\Player;

final class CombatManager extends Manager
{
    private float $kb;
    private float $verticalKbLimit;
    private int $attackCooldown;
    private int $fightDuration;

    private array $inFight = [];

    public function onLoad(): void
    {
        $combatInfos = Managers::CONFIG()->get("combat");
        $this->kb = $combatInfos["kb"];
        $this->verticalKbLimit = $combatInfos["vertical-kb-limit"];
        $this->attackCooldown = $combatInfos["attackcooldown"];
        $this->fightDuration = $combatInfos["fight-duration"];
    }

    public function updatePlayerFight(Player $player): void
    {
        $this->inFight[strtolower($player->getName())] = time() + $this->fightDuration;
    }

    public function removePlayerFight(Player $player): void
    {
        unset($this->inFight[strtolower($player->getName())]);
    }

    public function getPlayerFightTimeRemaining(Player $player): int
    {
        return $this->inFight($player) ? $this->inFight[strtolower($player->getName())] - time() : 0;
    }

    public function inFight(Player $player): bool
    {
        return isset($this->inFight[strtolower($player->getName())]) && $this->inFight[strtolower($player->getName())] - time() > 0;
    }

    public function getAllPlayersInFight(): array
    {
        return $this->inFight;
    }

    public function getKb(): float
    {
        return $this->kb;
    }

    public function getVerticalKbLimit(): float
    {
        return $this->verticalKbLimit;
    }

    public function getAttackCooldown(): int
    {
        return $this->attackCooldown;
    }
}