<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\managers\Manager;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\tasks\async\VoteAsyncTask;
use pocketmine\player\Player;

final class VoteManager extends Manager
{
    private string $key;

    public function onLoad(): void
    {
        $this->key = Managers::CONFIG()->get("vote-key");
    }

    public function testVote(Player $player): void
    {
        $player->getServer()->getAsyncPool()->submitTask(new VoteAsyncTask($this->key, $player->getName()));
    }
}