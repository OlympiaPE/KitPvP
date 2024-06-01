<?php

namespace Olympia\Kitpvp\traits;

use pocketmine\player\Player;

trait BlacklistTrait
{
    /** @var bool[] */
    private array $blacklisted = [];

    /**
     * @param Player $player
     * @return bool
     */
    public function isBlacklist(Player $player): bool
    {
        return ($this->blacklisted[$player->getName()] ?? microtime(true)) > microtime(true);
    }

    /**
     * @param Player $player
     * @param float $time
     */
    public function blacklist(Player $player, float $time): void
    {
        $this->blacklisted[$player->getName()] = microtime(true) + $time;
    }

    /**
     * @param Player $player
     */
    public function unblacklist(Player $player): void
    {
        if(isset($this->blacklisted[$player->getName()])) unset($this->blacklisted[$player->getName()]);
    }
}