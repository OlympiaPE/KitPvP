<?php

namespace Olympia\Kitpvp\managers\types;

use DateTime;
use Olympia\Kitpvp\managers\Manager;
use Olympia\Kitpvp\utils\Utils;
use pocketmine\player\Player;
use pocketmine\Server;

final class ModerationManager extends Manager
{
    private array $playersMute = [];
    private array $playersFreeze = [];

    private array $playersReportList = [];

    private bool $chatLock = false;

    public function onLoad(): void
    {
    }

    public function addBan(string $playerName, DateTime $dateTime, string $reason, string $staff): void
    {
        Server::getInstance()->getNameBans()->addBan($playerName, $reason, $dateTime, $staff);
        Server::getInstance()->getIPBans()->addBan($playerName, $reason, $dateTime, $staff);
    }

    public function removeBan(string $playerName): void
    {
        Server::getInstance()->getNameBans()->remove($playerName);
        Server::getInstance()->getIPBans()->remove($playerName);
    }

    public function isBanned(string $playerName): bool
    {
        return Server::getInstance()->getNameBans()->isBanned($playerName) || Server::getInstance()->getIPBans()->isBanned($playerName);
    }

    public function addMute(string $playerName, int $duration): void
    {
        $this->playersMute[strtolower($playerName)] = time() + $duration;
    }

    public function removeMute(string $playerName): void
    {
        unset($this->playersMute[strtolower($playerName)]);
    }

    public function isMute(string $playerName): bool
    {
        return array_key_exists(strtolower($playerName), $this->playersMute) && $this->playersMute[strtolower($playerName)] - time() > 0;
    }

    public function getMuteRemainingTime(string $playerName): string
    {
        if($this->isMute($playerName)) {
            $time = $this->playersMute[strtolower($playerName)] - time();
            return Utils::durationToString($time);
        }else{
            return "0 seconde";
        }
    }

    public function addFreeze(Player $player): void
    {
        $this->playersFreeze[] = strtolower($player->getName());
        $player->setNoClientPredictions();
    }

    public function removeFreeze(Player $player): void
    {
        unset($this->playersFreeze[array_search(strtolower($player->getName()), $this->playersFreeze)]);
        $player->setNoClientPredictions(false);
    }

    public function isFreeze(Player $player): bool
    {
        return in_array(strtolower($player->getName()), $this->playersFreeze);
    }

    public function setPlayerReportList(string $player, array $list): void
    {
        $this->playersReportList[strtolower($player)] = $list;
    }

    public function removePlayerReportList(string $player): void
    {
        unset($this->playersReportList[strtolower($player)]);
    }

    public function getPlayerReportList(string $player): array
    {
        return $this->playersReportList[strtolower($player)];
    }

    public function lockChat(): void
    {
        $this->chatLock = true;
    }

    public function unlockChat(): void
    {
        $this->chatLock = false;
    }

    public function isChatLocked(): bool
    {
        return $this->chatLock;
    }
}