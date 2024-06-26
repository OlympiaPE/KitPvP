<?php

namespace Olympia\Kitpvp\tournament;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\BoxsManager;
use Olympia\Kitpvp\managers\types\TournamentManager;
use Olympia\Kitpvp\utils\Utils;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\sound\Sound;
use pocketmine\world\sound\XpCollectSound;

final class Tournament
{
    private bool $started = false;
    private float $startIn;
    private string $type;
    private array $players = [];

    private array $fighters = [];
    private ?string $fightWinner = null;
    private int $fightNumber = 0;
    private array $qualified = [];
    private array $eliminated = [];

    private TaskHandler $taskHandler;

    public function __construct(string $hoster, string $type)
    {
        $this->type = $type;
        $this->startIn = microtime(true) + Managers::CONFIG()->getNested("tournament.delay-before-starting");

        Server::getInstance()->broadcastMessage(str_replace(
            ["{type}", "{hoster}"],
            [$type, $hoster],
            Managers::CONFIG()->getNested("messages.tournament-create")
        ));

        $this->handle();
    }

    public function handle(): void
    {
        $this->taskHandler = Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() {

            if (!$this->isStarted()) {

                $decimal = $this->getStartIn(true) - floor($this->getStartIn(true));

                if ($decimal >= 0.75 || $decimal < 0.25) {

                    switch ($this->getStartIn()) {

                        case 30:
                        case 10:
                            $players = $this->getPlayers();
                            $this->broadcastMessageToPlayers($players, str_replace(
                                "{time}",
                                Utils::durationToString($this->getStartIn()),
                                Managers::CONFIG()->getNested("messages.tournament-start-in")
                            ));
                            $this->broadcastSoundToPlayers($players, new ClickSound());
                            break;

                        case 5:
                        case 4:
                        case 3:
                        case 2:
                        case 1:
                            $players = $this->getPlayers();
                            $numberColor = match ($this->getStartIn()) {
                                5 => "§a",
                                4 => "§e",
                                3 => "§6",
                                2 => "§c",
                                1 => "§4"
                            };
                            $this->broadcastTitleToPlayers($players, "§7Commence dans", $numberColor . $this->getStartIn());
                            $this->broadcastMessageToPlayers($players, str_replace(
                                "{time}",
                                Utils::durationToString($this->getStartIn()),
                                Managers::CONFIG()->getNested("messages.tournament-start-in")
                            ));
                            $this->broadcastSoundToPlayers($players, new ClickSound());
                            break;

                        case 0:
                            $this->start();
                            break;
                    }
                }
            }else{
                if ($this->getStartIn() < -1) {

                    if (empty($this->fighters)) {
                        if (count($this->getPlayersNames()) >= 2) {
                            $this->startFight();
                        }else{
                            $this->end();
                        }
                    }

                    if (!is_null($this->fightWinner)) {

                        $fighters = $this->getFightersNames();
                        $winnerName = $fighters[0] === $this->fightWinner ? $fighters[0] : $fighters[1];
                        $winner = Server::getInstance()->getPlayerExact($winnerName);
                        $loserName = $fighters[0] !== $this->fightWinner ? $fighters[0] : $fighters[1];
                        $loser = Server::getInstance()->getPlayerExact($loserName);

                        $this->broadcastMessageToPlayers($this->getPlayers(true), str_replace(
                            ["{winner}", "{loser}"],
                            [$winnerName, $loserName],
                            Managers::CONFIG()->getNested("messages.tournament-fight-ended")
                        ));

                        $this->fighters = [];
                        $this->qualified[] = $winnerName;
                        $this->eliminated[] = $loserName;
                        $this->fightWinner = null;
                        unset($this->players[array_search($loserName, $this->players)]);

                        if ($winner instanceof Session) {
                            $this->teleportPlayerToTournament($winner);
                            $this->clearPlayer($winner);
                        }

                        if ($loser instanceof Session) {
                            $this->teleportPlayerToTournament($loser);
                            $this->clearPlayer($loser);
                        }
                    }
                }
            }
        }), 10);
    }

    public function start(): void
    {
        if (count($this->getPlayersNames()) >= Managers::CONFIG()->getNested("tournament.min-players")) {

            $this->setStarted();

            $players = $this->getPlayers();
            $this->broadcastSoundToPlayers($players, new XpCollectSound());
            $this->broadcastTitleToPlayers($players, "§2Bonne chance !");

            foreach ($players as $player) {
                $this->clearPlayer($player);
            }
        }else{

            $this->broadcastMessageToPlayers($this->getPlayers(), Managers::CONFIG()->getNested("messages.tournament-not-enough-players"));
            $this->end(false);
        }
    }

    public function end(bool $hasWinner = true): void
    {
        $this->taskHandler->cancel();
        Managers::TOURNAMENT()->removeTournament();

        foreach ($this->getPlayers(true) as $player) {

            $player->setInTournament(false);
            $this->clearPlayer($player);
            $this->teleportPlayerToSpawn($player);
        }

        if ($hasWinner) {

            $winnerName = end($this->players);

            $winner = Server::getInstance()->getPlayerExact($winnerName);
            if ($winner instanceof Session) {
                Managers::BOXS()->giveKey($winner, BoxsManager::BOX_EVENT);
            }

            $this->broadcastMessageToPlayers($this->getPlayers(true), str_replace(
                "{winner}",
                $winnerName,
                Managers::CONFIG()->getNested("messages.tournament-ended")
            ));
        }
    }

    public function startFight(): void
    {
        $this->fightNumber++;
        $this->fighters = $fighters = $this->getNextFighters();

        $this->broadcastTitleToPlayers(
            $this->getPlayers(true),
            "§7" . Utils::numberToOrdinal($this->fightNumber, true) . " combat",
            "§e$fighters[0] vs $fighters[1]",
            40
        );

        $scheduler = Loader::getInstance()->getScheduler();
        /** @var Session $fighter1 */
        $fighter1 = Server::getInstance()->getPlayerExact($fighters[0]);
        /** @var Session $fighter2 */
        $fighter2 = Server::getInstance()->getPlayerExact($fighters[1]);
        $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($scheduler, $fighter1, $fighter2) {

            if ($this->getType() === TournamentManager::TOURNAMENT_TYPE_SUMO) {
                $fighter1->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 2147483646, 255, false));
                $fighter2->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 2147483646, 255, false));
            }

            $baseKey = "tournament.type." . $this->getType();
            $spawnPositions = Managers::CONFIG()->getNested("$baseKey.spawn-positions");
            $worldName = Managers::CONFIG()->getNested("$baseKey.world");
            $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);

            $fighter1->teleport(new Position(
                $spawnPositions["fighter-one"]["x"],
                $spawnPositions["fighter-one"]["y"],
                $spawnPositions["fighter-one"]["z"],
                $world
            ));

            $fighter2->teleport(new Position(
                $spawnPositions["fighter-two"]["x"],
                $spawnPositions["fighter-two"]["y"],
                $spawnPositions["fighter-two"]["z"],
                $world
            ));

            foreach ([$fighter1, $fighter2] as $fighter) {
                $fighter->setNoClientPredictions();
                $fighter->sendTitle("§aDémarrage dans 3", 5, 20, 5);
            }

            $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($scheduler, $fighter1, $fighter2) {

                if (!is_null($this->fightWinner)) {
                    $this->cancelStartFight($fighter1, $fighter2);
                    return;
                }

                $fighter1->sendTitle("§6Démarrage dans 2", 5, 20, 5);
                $fighter2->sendTitle("§6Démarrage dans 2", 5, 20, 5);

                $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($scheduler, $fighter1, $fighter2) {

                    if (!is_null($this->fightWinner)) {
                        $this->cancelStartFight($fighter1, $fighter2);
                        return;
                    }

                    $fighter1->sendTitle("§cDémarrage dans 1", 5, 20, 5);
                    $fighter2->sendTitle("§cDémarrage dans 1", 5, 20, 5);

                    $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($scheduler, $fighter1, $fighter2) {

                        if (!is_null($this->fightWinner)) {
                            $this->cancelStartFight($fighter1, $fighter2);
                            return;
                        }

                        foreach ([$fighter1, $fighter2] as $fighter) {

                            $fighter->setNoClientPredictions(false);
                            $fighter->sendTitle("§eBonne chance !", 5, 15, 5);
                            $fighter->setHealth(20);
                            $fighter->setGamemode(GameMode::ADVENTURE);
                            Managers::TOURNAMENT()->givePlayerTournamentKit($fighter, $this->getType());
                        }
                    }), 20);
                }), 20);
            }), 20);
        }), 40);
    }

    public function cancelStartFight(Session $fighter1, Session $fighter2): void
    {
        foreach ([$fighter1, $fighter2] as $fighter) {
            if ($fighter->isOnline() && $fighter->isAlive()) {
                $fighter->setNoClientPredictions(false);
                $fighter->resetTitles();
            }
        }
    }

    /**
     * @return Session[]
     */
    public function getNextFighters(): array
    {
        $fighters = [];
        $needToFight = array_diff($this->getPlayersNames(), $this->getQualifiedNames(), $this->getEliminatedNames());

        if (empty($needToFight)) {
            $this->qualified = [];
            return $this->getNextFighters();
        }

        if (count($needToFight) === 1) {
            $this->qualified = [];
            $fighters[] = array_shift($needToFight);
            $needToFight = array_values(array_diff($this->getPlayersNames(), [$fighters[0]]));
            $fighters[] = $needToFight[array_rand($needToFight)];
        }else{
            $players = array_rand($needToFight, 2);
            $fighters[] = $needToFight[$players[0]];
            $fighters[] = $needToFight[$players[1]];
        }

        return $fighters;
    }

    public function getStartIn(bool $decimal = false): float|int
    {
        if ($decimal) {
            return $this->startIn - microtime(true);
        }else{
            return (int)(floor($this->startIn - microtime(true)));
        }
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function addPlayer(Session $player): void
    {
        $this->broadcastMessageToPlayers($this->getPlayers(), str_replace(
            "{player}",
            $player->getName(),
            Managers::CONFIG()->getNested("messages.tournament-join")
        ));

        $this->players[] = $player->getName();
        $this->teleportPlayerToTournament($player);
        $leaveItem = VanillaItems::NETHER_STAR()
            ->setCustomName("§cQuitter")
            ->setLore(["§7Clique droit pour quitter le tournois"]);
        $player->getInventory()->setItem(4, $leaveItem);
        $player->setGamemode(GameMode::ADVENTURE);
        $player->setInTournament();
        $player->sendMessage(Managers::CONFIG()->getNested("messages.tournament-join-player"));
    }

    public function removePlayer(Session $player, bool $causeDisconnect = false): void
    {
        if (in_array($player->getName(), $this->getPlayersNames())) {
            unset($this->players[array_search($player->getName(), $this->players)]);
        }

        if (in_array($player->getName(), $this->getEliminatedNames())) {
            unset($this->eliminated[array_search($player->getName(), $this->eliminated)]);
        }

        $this->clearPlayer($player);

        $this->broadcastMessageToPlayers($this->getPlayers(true), str_replace(
            "{player}",
            $player->getName(),
            Managers::CONFIG()->getNested("messages.tournament-leave")
        ));

        if (!$causeDisconnect) {

            $player->setInTournament(false);
            $this->teleportPlayerToSpawn($player);
            $player->sendMessage(Managers::CONFIG()->getNested("messages.tournament-leave-player"));
        }
    }

    public function getPlayersNames(): array
    {
        return $this->players;
    }

    /**
     * @return Session[]
     */
    public function getPlayers(bool $includeEliminated = false): array
    {
        $players = [];
        $server = Server::getInstance();
        foreach ($this->getPlayersNames() as $playerName) {
            $player = $server->getPlayerExact($playerName);
            if ($player instanceof Session) {
                $players[$playerName] = $player;
            }
        }
        if ($includeEliminated) {
            foreach ($this->getEliminatedNames() as $eliminatedName) {
                $eliminated = $server->getPlayerExact($eliminatedName);
                if ($eliminated instanceof Session) {
                    $players[$eliminatedName] = $eliminated;
                }
            }
        }
        return $players;
    }

    public function getFightersNames(): array
    {
        return $this->fighters;
    }

    public function getQualifiedNames(): array
    {
        return $this->qualified;
    }

    public function getEliminatedNames(): array
    {
        return $this->eliminated;
    }

    public function setStarted(): void
    {
        $this->started = true;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }


    // USEFUL FUNCTIONS

    public function teleportPlayerToTournament(Session $player): void
    {
        $baseKey = "tournament.type." . $this->getType();
        $positions = Managers::CONFIG()->getNested("$baseKey.spawn-positions.player");
        $worldName = Managers::CONFIG()->getNested("$baseKey.world");
        $x = (int)$positions["x"] + 0.5;
        $y = (int)$positions["y"];
        $z = (int)$positions["z"] + 0.5;
        $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
        $position = new Position($x, $y, $z, $world);
        $player->teleport($position);
    }

    public function teleportPlayerToSpawn(Session $player): void
    {
        $spawnInfos = Managers::CONFIG()->get("spawn");
        $x = (int)$spawnInfos["x"];
        $y = (int)$spawnInfos["y"];
        $z = (int)$spawnInfos["z"];
        $spawnWorld = Server::getInstance()->getWorldManager()->getWorldByName($spawnInfos["world"]);
        $position = new Position($x, $y, $z, $spawnWorld);
        $player->teleport($position);
    }

    /**
     * @param Session[] $players
     * @param string $message
     * @return void
     */
    public function broadcastMessageToPlayers(array $players, string $message): void
    {
        foreach ($players as $player) {
            $player->sendMessage($message);
        }
    }

    /**
     * @param Session[] $players
     * @param string $title
     * @param string $subTitle
     * @param int $stay
     * @return void
     */
    public function broadcastTitleToPlayers(array $players, string $title, string $subTitle = "", int $stay = 20): void
    {
        foreach ($players as $player) {
            $player->sendTitle($title, $subTitle, 5, $stay, 5);
        }
    }

    /**
     * @param Session[] $players
     * @param Sound $sound
     * @return void
     */
    public function broadcastSoundToPlayers(array $players, Sound $sound): void
    {
        foreach ($players as $player) {
            $player->broadcastSound($sound, [$player]);
        }
    }

    public function clearPlayer(Session $player): void
    {
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        $player->getCursorInventory()->clearAll();

        $player->getEffects()->remove(VanillaEffects::RESISTANCE());
        $player->getEffects()->remove(VanillaEffects::ABSORPTION());
        $player->getEffects()->remove(VanillaEffects::REGENERATION());
        $player->getEffects()->remove(VanillaEffects::SPEED());
    }


    // FUNCTIONS FOR LISTENERS

    public function isDamageable(Session $player): bool
    {
        return in_array($player->getName(), $this->fighters);
    }

    public function getKbInfos(): array
    {
        return Managers::CONFIG()->getNested("tournament.type.{$this->getType()}.kb");
    }

    public function setFightWinner(string $fightWinner): void
    {
        $this->fightWinner = $fightWinner;
    }
}