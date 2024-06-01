<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\managers\ManageLoader;
use Olympia\Kitpvp\tasks\async\VoteAsyncTask;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

final class VoteManager extends ManageLoader
{
    use SingletonTrait;

    private string $key;

    public function onInit(): void
    {
        $this->key = ConfigManager::getInstance()->get("vote-key");
    }

    public function testVote(Player $player): void
    {
        $player->getServer()->getAsyncPool()->submitTask(new VoteAsyncTask($this->key, $player->getName()));
    }
}