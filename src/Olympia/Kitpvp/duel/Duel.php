<?php

namespace Olympia\Kitpvp\duel;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\DuelManager;
use Olympia\Kitpvp\utils\WorldUtils;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\GameMode;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\sound\Sound;
use pocketmine\world\sound\XpCollectSound;

final class Duel
{
    private int $id;
    private array $players;
    private int $mise;
    private int $type;
    private int $state;
    private int $creationTimestamp;
    private string $mapConfigKey;
    private string $mapName;

    private string $winner = "";
    private array $spectators = [];

    public function __construct(int $id, Session $player, Session $target, int $mise, int $type)
    {
        $this->id = $id;
        $this->players = [$player->getName(), $target->getName()];
        $this->mise = $mise;
        $this->type = $type;
        $this->state = DuelStates::PENDING;
        $this->creationTimestamp = time();
        $this->mapConfigKey = "duels-maps." . ($type == DuelManager::DUEL_TYPE_SUMO ? "sumo" : "basic");
        $this->mapName = array_rand(Managers::CONFIG()->getNested($this->mapConfigKey));
    }

    public function start(): void
    {
        $this->setState(DuelStates::STARTING);
        $this->broadcastPlayersMessage(Managers::CONFIG()->getNested("messages.duel-start"));

        $server = Server::getInstance();
        $baseMapName = $this->getMapName();
        $baseMapWorld = $server->getWorldManager()->getWorldByName($baseMapName);

        if (is_null($baseMapWorld)) {
            $this->broadcastPlayersMessage(Managers::CONFIG()->getNested("messages.player-encounters-error"));
            Managers::DUEL()->deleteDuel(Managers::DUEL()->getDuelIndexById($this->getId()));
            return;
        }

        if ($baseMapWorld->isLoaded()) {
            $server->getWorldManager()->unloadWorld($baseMapWorld);
        }

        $mapWorldName = WorldUtils::copyWorld($baseMapName, $this->getWorldName());
        $server->getWorldManager()->loadWorld($mapWorldName);
        $mapWorld = WorldUtils::getWorldByFolderName($mapWorldName);
        $mapWorld->setDisplayName($mapWorldName);

        $players = $this->getPlayers();
        $player1 = $players[0];
        $player2 = $players[1];

        $mapInfos = Managers::CONFIG()->getNested($this->getMapConfigKey());
        $player1Pos = $mapInfos["players-spawn"]["1"];
        $player2Pos = $mapInfos["players-spawn"]["2"];

        $player1->teleport(new Position($player1Pos["x"], $player1Pos["y"], $player1Pos["z"], $mapWorld));
        $player2->teleport(new Position($player2Pos["x"], $player2Pos["y"], $player2Pos["z"], $mapWorld));

        foreach ($players as $p) {
            $p->setNoClientPredictions();
            $p->setDuelState(Session::DUEL_STATE_FIGHTER);
            $p->setDuelId($this->getId());
            $p->setGamemode(GameMode::ADVENTURE);

            if ($this->getType() === DuelManager::DUEL_TYPE_SUMO) {
                $p->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 2147483646, 255, false));
            }
        }

        $scheduler = Loader::getInstance()->getScheduler();

        $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($scheduler, $player1, $player2) {

            $this->broadcastPlayersTitle(Managers::CONFIG()->getNested("messages.duel-starting-title-3"));
            $this->broadcastPlayersSound(new ClickSound());

            $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($scheduler, $player1, $player2) {

                $this->broadcastPlayersTitle(Managers::CONFIG()->getNested("messages.duel-starting-title-2"));
                $this->broadcastPlayersSound(new ClickSound());

                $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($scheduler, $player1, $player2) {

                    $this->broadcastPlayersTitle(Managers::CONFIG()->getNested("messages.duel-starting-title-1"));
                    $this->broadcastPlayersSound(new ClickSound());

                    $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($scheduler, $player1, $player2) {

                        $this->broadcastPlayersTitle(Managers::CONFIG()->getNested("messages.duel-starting-title-gl"), 30);
                        $this->broadcastPlayersSound(new XpCollectSound());

                        $player1->setNoClientPredictions(false);
                        $player2->setNoClientPredictions(false);

                        Managers::DUEL()->givePlayerDuelKit($player1, $this->getType());
                        Managers::DUEL()->givePlayerDuelKit($player2, $this->getType());

                        $this->setState(DuelStates::IN_PROGRESS);
                    }), 20);
                }), 20);
            }), 20);
        }), 10);
    }

    public function end(bool $serverShutdownCause = false): void
    {
        $this->setState(DuelStates::FINISHED);

        if (!$serverShutdownCause) {

            $players = array_merge($this->getPlayers(), $this->getSpectators());

            foreach ($players as $p) {

                $p->setDuelState(Session::DUEL_STATE_NONE);
                $p->resetDuelId();
                $p->getInventory()->clearAll();
                $p->getArmorInventory()->clearAll();
                $p->getOffHandInventory()->clearAll();
                $p->getCursorInventory()->clearAll();
                $p->setGamemode(GameMode::ADVENTURE);
                $p->getEffects()->remove(VanillaEffects::RESISTANCE());
                $p->getEffects()->remove(VanillaEffects::POISON());
                $p->setOnFire(0);
            }

            if ($this->getWinner() !== "") {

                $fighters = $this->getPlayers();
                $winner = $fighters[0]->getName() === $this->getWinner() ? $fighters[0] : $fighters[1];
                $loser = $fighters[0]->getName() !== $this->getWinner() ? $fighters[0] : $fighters[1];

                $winner->setHealth(20);

                if ($this->getMise() > 0) {

                    $winner->addMoney($this->getMise());
                    $loser->removeMoney($this->getMise());
                }

                $this->broadcastPlayersAndSpectatorsMessage(
                    str_replace(
                        ["{winner}", "{mise}"],
                        [$this->getWinner(), $this->getMise()],
                        Managers::CONFIG()->getNested("messages.duel-ended")
                    )
                );
            }
        }

        // Also teleports players to the spawn
        WorldUtils::removeWorld($this->getWorldName());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlayersName(): array
    {
        return $this->players;
    }

    /**
     * @return Session[]
     */
    public function getPlayers(): array
    {
        $server = Server::getInstance();
        return [
            $server->getPlayerExact($this->players[0]),
            $server->getPlayerExact($this->players[1])
        ];
    }

    /**
     * @return Session[]
     */
    public function getSpectators(): array
    {
        $spectators = [];
        $server = Server::getInstance();
        foreach ($this->spectators as $spectatorName) {
            $spectators[] = $server->getPlayerExact($spectatorName);
        }
        return $spectators;
    }

    public function broadcastPlayersMessage(string $message): void
    {
        foreach ($this->getPlayers() as $player) {
            $player->sendMessage($message);
        }
    }

    public function broadcastPlayersAndSpectatorsMessage(string $message): void
    {
        $players = array_merge($this->getPlayers(), $this->getSpectators());
        foreach ($players as $player) {
            $player->sendMessage($message);
        }
    }

    public function broadcastPlayersTitle(string $title, int $stay = 20): void
    {
        foreach ($this->getPlayers() as $player) {
            $player->sendTitle($title, "", 5, $stay, 5);
        }
    }

    public function broadcastPlayersSound(Sound $sound): void
    {
        foreach ($this->getPlayers() as $player) {
            $player->broadcastSound($sound, [$player]);
        }
    }

    public function getMise(): int
    {
        return $this->mise;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function getCreationTimestamp(): int
    {
        return $this->creationTimestamp;
    }

    public function getMapConfigKey(): string
    {
        return $this->mapConfigKey . "." . $this->getMapName();
    }

    public function getMapName(): string
    {
        return $this->mapName;
    }

    public function getWorldName(): string
    {
        return $this->getMapName() . "-" . $this->getId();
    }

    public function setWinner(string $winner): void
    {
        $this->winner = $winner;
    }

    public function getWinner(): string
    {
        return $this->winner;
    }

    public function getKbInfos(): array
    {
        return Managers::CONFIG()->getNested("duels-kb." . str_replace(" ", "-", strtolower(Managers::DUEL()->getDuelTypeDisplayName($this->getType()))));
    }

    public function addSpectator(Session $player): void
    {
        $player->setDuelState(Session::DUEL_STATE_SPECTATOR);
        $player->setDuelId($this->getId());

        $mapInfos = Managers::CONFIG()->getNested($this->getMapConfigKey());
        $pos = $mapInfos["players-spawn"]["spectator"];
        $world = $player->getServer()->getWorldManager()->getWorldByName($this->getWorldName());
        $player->teleport(new Position($pos["x"], $pos["y"], $pos["z"], $world));

        $player->setGamemode(GameMode::SPECTATOR);
        $player->sendMessage(Managers::CONFIG()->getNested("messages.duel-spectator-join"));

        $this->broadcastPlayersMessage(str_replace(
            "{player}",
            $player->getName(),
            Managers::CONFIG()->getNested("messages.duel-player-now-spectator")
        ));

        $this->spectators[] = $player->getName();
    }

    public function removeSpectator(Session $player): void
    {
        $player->setDuelState(Session::DUEL_STATE_NONE);
        $player->resetDuelId();

        // The '/spawn' command (used to remove the spectator) teleports already the player

        $player->setGamemode(GameMode::ADVENTURE);
        $player->sendMessage(Managers::CONFIG()->getNested("messages.duel-spectator-quit"));

        $this->broadcastPlayersMessage(str_replace(
            "{player}",
            $player->getName(),
            Managers::CONFIG()->getNested("messages.duel-player-no-longer-spectator")
        ));

        unset($this->spectators[array_search($player->getName(), $this->spectators)]);
    }
}