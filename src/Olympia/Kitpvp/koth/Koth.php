<?php

namespace Olympia\Kitpvp\koth;

use Olympia\Kitpvp\Core;
use Olympia\Kitpvp\entities\objects\FloatingText;
use Olympia\Kitpvp\managers\types\BoxsManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\EventsManager;
use Olympia\Kitpvp\managers\types\FloatingTextManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\math\Vector3;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
use pocketmine\world\World;

class Koth
{
    private ?string $player = null;

    private ?int $captureTime = null;

    private TaskHandler $taskHandler;

    private string $floatingTextId;

    public function __construct()
    {
        EventsManager::getInstance()->removeKothFloatingText();
        $this->floatingTextId = FloatingTextManager::getInstance()->createFloatingText(
            FloatingTextManager::getInstance()->getLocationByCoordinates(
                ConfigManager::getInstance()->getNested("koth.floating-text.x"),
                ConfigManager::getInstance()->getNested("koth.floating-text.y"),
                ConfigManager::getInstance()->getNested("koth.floating-text.z")
            ),
            "§6KOTH",
            function (FloatingText $entity) {

                $timeRemaining = ceil(($this->getCaptureTimeRemaining() ?? 240) / 6);
                $captureTimeRemaining = "";
                for($i = 40; $i > 0; $i--) {
                    if($i > $timeRemaining) {
                        $captureTimeRemaining .= "§a|";
                    }else{
                        $captureTimeRemaining .= "§c|";
                    }
                }
                $player = $this->getPlayer() ?? "Aucun";
                $entity->setNameTag("§6KOTH\n§fJoueur : §6$player\n" . $captureTimeRemaining);
            },
            20,
            $this
        );

        Server::getInstance()->broadcastMessage(ConfigManager::getInstance()->getNested("messages.koth-start"));

        $this->taskHandler = Core::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () {

            $players = [];
            $zone = $this->getZone();
            $first = $zone["first"];
            $second = $zone["second"];

            /** @var OlympiaPlayer $player */
            foreach ($this->getWorld()->getPlayers() as $player) {
                $toCheck = $player->getPosition();
                if(
                    $first->getFloorX() <= floor($toCheck->getX()) &&
                    $second->getFloorX() >= floor($toCheck->getX()) &&
                    $first->getFloorY() <= floor($toCheck->getY()) &&
                    $second->getFloorY() >= floor($toCheck->getY()) &&
                    $first->getFloorZ() <= floor($toCheck->getZ()) &&
                    $second->getFloorZ() >= floor($toCheck->getZ()) &&
                    $player->isAlive()
                ) {
                    $players[] = strtolower($player->getName());
                }
            }

            if(empty($players)) {
                $this->resetAll();
            }elseif(count($players) === 1) {
                $playerName = array_shift($players);
                $player = Server::getInstance()->getPlayerExact($playerName);
                if(!is_null($player)) {
                    if(is_null($this->getPlayer()) || strtolower($this->getPlayer()) !== $playerName) {
                        $this->setPlayer($player->getName());
                        $this->setCaptureTime(time());
                    }else{
                        if($this->getCaptureTimeRemaining() <= 0) {
                            $this->end();
                        }
                    }
                }else{
                    $this->resetAll();
                }
            }else{
                if(in_array(strtolower($this->getPlayer() ?? ""), $players)) {
                    if($this->getCaptureTimeRemaining() <= 0) {
                        $this->end();
                    }
                }else{
                    $this->resetAll();
                }
            }
        }), 20);
    }

    private function end(): void
    {
        $playerName = $this->getPlayer();
        $player = Server::getInstance()->getPlayerExact($playerName);

        Server::getInstance()->broadcastMessage(str_replace(
            "{winner}",
            $playerName ?? "",
            ConfigManager::getInstance()->getNested("messages.koth-captured")
        ));

        if ($player instanceof OlympiaPlayer) {

            $rewards = ConfigManager::getInstance()->getNested("koth.rewards");
            $money = $rewards["money"];
            $keyCount = $rewards["key-event"];

            $player->addMoney($money);
            BoxsManager::getInstance()->giveKey($player, BoxsManager::BOX_EVENT, $keyCount);

            $player->sendMessage(str_replace(
                ["{money}", "{keyCount}"],
                [$money, $keyCount],
                ConfigManager::getInstance()->getNested("messages.koth-reward")
            ));
        }

        $this->taskHandler->cancel();
        EventsManager::getInstance()->removeKoth();

        FloatingTextManager::getInstance()->removeFloatingText($this->floatingTextId);
        EventsManager::getInstance()->createKothFloatingText();
    }

    public function getWorldName(): string
    {
        return ConfigManager::getInstance()->getNested("koth.world");
    }

    public function getWorld(): World
    {
        return Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName());
    }

    public function getPlayer(): ?string
    {
        return $this->player;
    }

    public function setPlayer(?string $player): void
    {
        $this->player = $player;
    }

    /**
     * @return Vector3[]
     */
    public function getZone(): array
    {
        $zone = ConfigManager::getInstance()->getNested("koth.zone");
        return ["first" => new Vector3($zone["min-x"], $zone["min-y"], $zone["min-z"]), "second" => new Vector3($zone["max-x"], $zone["max-y"], $zone["max-z"])];
    }

    public function setCaptureTime(?int $time): void
    {
        $this->captureTime = $time;
    }

    public function getCaptureTimeRemaining(): ?int
    {
        return !is_null($this->captureTime)
            ? ConfigManager::getInstance()->getNested("koth.capture-time") - (time() - $this->captureTime)
            : null;
    }

    public function resetAll(): void
    {
        if(!is_null($this->getPlayer())) {
            $this->setPlayer(null);
        }
        if(!is_null($this->getCaptureTimeRemaining())) {
            $this->setCaptureTime(null);
        }
    }
}