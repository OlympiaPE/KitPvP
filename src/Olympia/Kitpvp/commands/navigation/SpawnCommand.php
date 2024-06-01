<?php

namespace Olympia\Kitpvp\commands\navigation;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\Position;

class SpawnCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("spawn", "Spawn command", "/spawn", ['hub']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {

            $spawnInfos = ConfigManager::getInstance()->get("spawn");
            $world = $sender->getServer()->getWorldManager()->getWorldByName($spawnInfos["world"]);

            if ($world) {

                $x = (int)$spawnInfos["x"];
                $y = (int)$spawnInfos["y"];
                $z = (int)$spawnInfos["z"];
                $position = new Position($x, $y, $z, $world);

                $sender->teleport($position);
                $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.spawn"));
            }else{
                $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.player-encounters-error"));
            }
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}