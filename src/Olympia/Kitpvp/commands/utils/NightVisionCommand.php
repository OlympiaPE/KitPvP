<?php

namespace Olympia\Kitpvp\commands\utils;

use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\command\CommandSender;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;

class NightVisionCommand extends OlympiaCommand
{
    public function __construct()
    {
        parent::__construct("nightvision", "Nightvision command", "/nightvision", ['nv']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {

            $effect = new EffectInstance(VanillaEffects::NIGHT_VISION(), 2147483646, 0, false);
            $sender->getEffects()->add($effect);

            $sender->sendMessage(Managers::CONFIG()->getNested("messages.nightvision"));
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }
}