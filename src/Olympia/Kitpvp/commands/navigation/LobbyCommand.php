<?php

namespace Olympia\Kitpvp\commands\navigation;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class LobbyCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("lobby", "Lobby command", "/lobby");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {

            $lobbyInfos = Managers::CONFIG()->get("lobby");
            $sender->transfer($lobbyInfos["ip"], $lobbyInfos["port"]);
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}