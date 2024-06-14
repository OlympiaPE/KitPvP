<?php

namespace Olympia\Kitpvp\commands\moderation;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\utils\constants\Permissions;
use pocketmine\command\CommandSender;

class FreezeCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_FREEZE;
        parent::__construct("freeze", "Freeze command", "/freeze [player]");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(isset($args[0])) {

            $playerName = $args[0];

            /** @var $player ?Session */
            if(!is_null($player = $sender->getServer()->getPlayerByPrefix($playerName))) {

                if(!Managers::MODERATION()->isFreeze($player)) {

                    $playerName = $player->getName();
                    $staff = $sender->getName();
                    $sender->sendMessage(str_replace(
                        "{player}",
                        $playerName,
                        Managers::CONFIG()->getNested("messages.freeze-staff")
                    ));

                    Managers::MODERATION()->addFreeze($player);
                    $player->sendMessage(str_replace(
                        "{staff}",
                        $staff,
                        Managers::CONFIG()->getNested("messages.freeze-victim")
                    ));
                    Managers::WEBHOOK()->sendMessage("Freeze", "**Joueur** : $playerName\n**Staff** : $staff", WebhookManager::CHANNEL_LOGS_SANCTIONS);
                }else{
                    $sender->sendMessage(Managers::CONFIG()->getNested("messages.freeze-already-freeze"));
                }
            }else{
                $sender->sendMessage(Managers::CONFIG()->getNested("messages.player-not-found"));
            }
        }else{
            $this->sendUsageMessage($sender);
        }
    }
}