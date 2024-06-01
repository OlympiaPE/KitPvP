<?php

namespace Olympia\Kitpvp\commands\admin;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\BoxsManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class GivekeyCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_GIVEKEY;
        parent::__construct("givekey", "Givekey command", "/givekey [all/playerName] [vote/epic/event/shop/cosmetic]", ["key"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (count($args) > 1 &&
            (!is_bool($args[1])) &&
            (in_array(strtolower($args[1]), ["vote", "epic", "event", "shop", "cosmetic"])) &&
            (!isset($args[2]) || is_numeric($args[2]))
        ){
            $box = match($args[1]) {
                "vote" => BoxsManager::BOX_VOTE,
                "epic" => BoxsManager::BOX_EPIC,
                "event" => BoxsManager::BOX_EVENT,
                "shop" => BoxsManager::BOX_SHOP,
                "cosmetic" => BoxsManager::BOX_COSMETIC,
            };

            $target = $args[0];
            $quantity = $args[2] ?? 1;

            /** @var OlympiaPlayer $player */
            if($target === "all" || $target === "everyone") {

                foreach (Server::getInstance()->getOnlinePlayers() as $player) {

                    BoxsManager::getInstance()->giveKey($player, $box, $quantity);
                }
            }elseif(!is_null($player = Server::getInstance()->getPlayerByPrefix($target))) {

                BoxsManager::getInstance()->giveKey($player, $box, $quantity);
            }else{

                $message = ConfigManager::getInstance()->getNested("messages.player-not-found");
                $sender->sendMessage($message);
            }
        }else{

            $this->sendUsageMessage($sender);
        }
    }
}