<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\managers\ManageLoader;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;

final class MoneyManager extends ManageLoader
{
    use SingletonTrait;

    private array $playersMoneyData;

    public function onInit(): void
    {
        $moneyData = [];
        $path = Server::getInstance()->getDataPath() . "/players";
        foreach (scandir($path) as $file) {
            if ($file != '.' && $file != '..') {
                $name = pathinfo($file, PATHINFO_FILENAME);
                if($this->hasOfflinePlayerMoneyData($name)) {
                    $moneyData[$name] = $this->getOfflinePlayerMoney($name);
                }
            }
        }
        $this->playersMoneyData = $moneyData;
    }

    /**
     * @param string $player
     * @return int|null
     */
    public function getOfflinePlayerMoney(string $player): int|null
    {
        if(isset($this->playersMoneyData[$player])) {
            return $this->playersMoneyData[$player];
        }else{
            $data = Server::getInstance()->getOfflinePlayerData($player)?->safeClone();
            $properties = $data?->getCompoundTag("properties");
            return (int)$properties?->getString("money") ?? 0;
        }
    }

    /**
     * @param string $player
     * @param int $money
     * @return void
     */
    public function setOfflinePlayerMoney(string $player, int $money): void
    {
        $data = Server::getInstance()->getOfflinePlayerData($player)->safeClone();
        $properties = $data->getCompoundTag("properties");
        $properties->setString("money", (string)$money);
        Server::getInstance()->saveOfflinePlayerData($player, $data);
    }

    /**
     * @param string $player
     * @param int $money
     * @return void
     */
    public function addOfflinePlayerMoney(string $player, int $money): void
    {
        $data = Server::getInstance()->getOfflinePlayerData($player)->safeClone();
        $properties = $data->getCompoundTag("properties");
        $properties->setString("money", (string)($this->getOfflinePlayerMoney($player) + $money));
        Server::getInstance()->saveOfflinePlayerData($player, $data);
    }

    /**
     * @param string $player
     * @param int $money
     * @return void
     */
    public function removeOfflinePlayerMoney(string $player, int $money): void
    {
        $data = Server::getInstance()->getOfflinePlayerData($player)->safeClone();
        $properties = $data->getCompoundTag("properties");
        $properties->setString("money", (string)($this->getOfflinePlayerMoney($player) - $money));
        Server::getInstance()->saveOfflinePlayerData($player, $data);
    }

    /**
     * @param string $player
     * @return bool
     */
    public function hasOfflinePlayerMoneyData(string $player): bool
    {
        $data = Server::getInstance()->getOfflinePlayerData($player)?->safeClone();
        return !is_null($data?->getCompoundTag("properties"));
    }

    public function inPlayersMoneyData(string $name): bool
    {
        return isset($this->playersMoneyData[strtolower($name)]);
    }

    /**
     * @param string $name
     * @return void
     */
    public function updatePlayerMoneyData(string $name): void
    {
        $name = strtolower($name);
        if(!is_null($player = Server::getInstance()->getPlayerExact($name))) {
            /** @var OlympiaPlayer $player */
            $this->playersMoneyData[$name] = $player->getMoney();
        }else{
            $this->playersMoneyData[$name] = $this->getOfflinePlayerMoney($name);
        }
    }

    /**
     * @return array
     */
    public function getPlayersMoneyData(): array
    {
        return $this->playersMoneyData;
    }
}