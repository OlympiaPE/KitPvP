<?php

namespace Olympia\Kitpvp\commands\admin;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\constants\Permissions;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class ClearEntities extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_CLEARENTITIES;
        parent::__construct("clearentities", "Clearentities command (warning: it removes floatings text and boxs)", "/clearentities", ['ce']);
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $entitiesAmount = 0;

        foreach ($sender->getServer()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {
                if (!$entity instanceof Player) {
                    $entity->flagForDespawn();
                    $entitiesAmount++;
                }
            }
        }

        Managers::FLOATING_TEXT()->resetAllFloatingTexts();

        $sender->sendMessage(str_replace(
            "{amount}",
            (string)$entitiesAmount,
            Managers::CONFIG()->getNested("messages.clear-entities")
        ));
    }
}