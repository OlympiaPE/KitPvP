<?php

namespace Olympia\Kitpvp\entities;

use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\scheduler\ClosureTask;

class SessionCooldowns
{
    public const COOLDOWN_ENDERPEARL = 0;
    public const COOLDOWN_NOTCH = 1;
    public const COOLDOWN_KIT_REFILL = 2;
    public const COOLDOWN_KIT_HOURLY = 3;
    public const COOLDOWN_KIT_DAILY = 4;
    public const COOLDOWN_KIT_WEEKLY = 5;
    public const COOLDOWN_KIT_ARCHER = 6;
    public const COOLDOWN_KIT_JOUEUR = 7;
    public const COOLDOWN_KIT_ANGES = 8;
    public const COOLDOWN_KIT_DIABLOTINS = 9;
    public const COOLDOWN_KIT_ARCHANGES = 10;
    public const COOLDOWN_KIT_PERSEPHONE = 11;
    public const COOLDOWN_KIT_POSEIDON = 12;
    public const COOLDOWN_KIT_HECATE = 13;
    public const COOLDOWN_KIT_ZEUS = 14;
    public const COOLDOWN_KIT_HADES = 15;
    public const COOLDOWN_HOST_TOURNAMENT = 16;

    private Session $player;

    private array $cooldownsList;
    
    public function __construct(Session $player)
    {
        $this->player = $player;
        $this->cooldownsList = Managers::DATABASE()->getUuidData($player->getUniqueId()->toString(), "cooldowns", []);
    }

    public function getCooldown(int $id): int
    {
        return $this->hasCooldown($id) ? $this->cooldownsList[$id] - time() : 0;
    }

    public function setCooldown(int $id, int $time, string $messageStart = "", string $messageEnd = ""): void
    {
        $this->cooldownsList[$id] = time() + $time;

        if($messageStart !== "") {
            $this->player->sendMessage($messageStart);
        }

        if($messageEnd !== "") {
            Loader::getInstance()->getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($messageEnd): void {
                if($this->player->isOnline()) {
                    $this->player->sendMessage($messageEnd);
                }
            }), $time * 20);
        }
    }

    public function hasCooldown(int $id): bool
    {
        return isset($this->cooldownsList[$id]) && $this->cooldownsList[$id] - time() > 0;
    }

    public function saveAllCooldowns(): void
    {
        Managers::DATABASE()->setUuidData($this->player->getUniqueId()->toString(), "cooldowns", $this->cooldownsList);
    }
}