<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\entities\npc\NPC;
use Olympia\Kitpvp\managers\ManageLoader;
use pocketmine\player\Player;
use JsonException;
use pocketmine\utils\SingletonTrait;

final class NPCManager extends ManageLoader
{
    use SingletonTrait;

    /** @var NPC[] $npcList */
    private array $npcList;

    private array $wantId = [];

    public function onInit(): void
    {
    }

    private function getNextNpcId(): int
    {
        return empty($this->npcList) ? 0 : array_key_last($this->npcList) + 1;
    }

    public function getNpcExists(int $id): bool
    {
        return isset($this->npcList[$id]);
    }

    public function loadNpc(NPC $npc): void
    {
        $this->npcList[$npc->getNpcId()] = $npc;
    }

    /**
     * @throws JsonException
     */
    public function createNpc(Player $spawner, string $name): int
    {
        $id = $this->getNextNpcId();
        $skinBytes = unpack("C*", $spawner->getSkin()->getSkinData());

        $this->npcList[$id] = $npc = new NPC($spawner->getLocation(), null, [
            "id" => $id,
            "name" => $name,
            "skin" => $skinBytes,
            "commands" => [],
        ]);
        $npc->spawnToAll();

        return $id;
    }

    public function deleteNpc(int $id): void
    {
        $this->npcList[$id]->close();
        unset($this->npcList[$id]);
    }

    public function addNpcCommand(int $id, string $command): void
    {
        $this->npcList[$id]->addNpcCommand($command);
    }

    public function addPlayerWantId(string $playerName): void
    {
        $this->wantId[] = strtolower($playerName);
    }

    public function removePlayerWantId(string $playerName): void
    {
        unset($this->wantId[array_search(strtolower($playerName), $this->wantId)]);
    }

    public function getPlayerWantId(string $playerName): bool
    {
        return in_array(strtolower($playerName), $this->wantId);
    }
}