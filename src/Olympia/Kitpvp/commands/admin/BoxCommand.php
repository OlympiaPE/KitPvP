<?php

namespace Olympia\Kitpvp\commands\admin;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\BoxsManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;

class BoxCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_BOX;
        parent::__construct("box", "Box command", "/box [spawn/delete] [vote/epic/event/shop/cosmetic] (orientation: 0-360)");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if($sender instanceof OlympiaPlayer) {
            if(
                (isset($args[0]) && in_array($args[0], ["spawn", "delete"])) &&
                (isset($args[1]) && in_array($args[1], ["vote", "epic", "event", "shop", "cosmetic"])) &&
                (!isset($args[2]) || ($args[2] >= 0 && $args[2] <= 360))
            ) {
                $box = match($args[1]) {
                    "vote" => BoxsManager::BOX_VOTE,
                    "epic" => BoxsManager::BOX_EPIC,
                    "event" => BoxsManager::BOX_EVENT,
                    "shop" => BoxsManager::BOX_SHOP,
                    "cosmetic" => BoxsManager::BOX_COSMETIC,
                };

                $orientation = $args[2] ?? 0;

                if($args[0] === "spawn") {
                    BoxsManager::getInstance()->spawnBox($box, $sender->getPosition(), $orientation);
                    $sender->sendMessage(str_replace("{box}", $args[1], ConfigManager::getInstance()->getNested("messages.spawn-box")));
                }else{
                    BoxsManager::getInstance()->deleteBox($box);
                    $sender->sendMessage(str_replace("{box}", $args[1], ConfigManager::getInstance()->getNested("messages.delete-box")));
                }
            }else{

                $this->sendUsageMessage($sender);
            }
        }
    }
}