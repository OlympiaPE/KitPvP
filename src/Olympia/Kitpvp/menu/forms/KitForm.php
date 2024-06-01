<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\libs\Vecnavium\FormsUI\SimpleForm;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\KitsManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\player\PlayerCooldowns;
use Olympia\Kitpvp\utils\Utils;

class KitForm extends Form
{
    public static function sendBaseMenu(OlympiaPlayer $player, ...$infos): void
    {
        $form = new SimpleForm(function (OlympiaPlayer $player, int $data = null) {

            if ($data === null)
                return true;

            $kitCooldown = match ($data) {
                KitsManager::KIT_REFILL => PlayerCooldowns::COOLDOWN_KIT_REFILL,
                KitsManager::KIT_HOURLY => PlayerCooldowns::COOLDOWN_KIT_HOURLY,
                KitsManager::KIT_DAILY => PlayerCooldowns::COOLDOWN_KIT_DAILY,
                KitsManager::KIT_WEEKLY => PlayerCooldowns::COOLDOWN_KIT_WEEKLY,
                KitsManager::KIT_ARCHER => PlayerCooldowns::COOLDOWN_KIT_ARCHER,
                KitsManager::KIT_JOUEUR => PlayerCooldowns::COOLDOWN_KIT_JOUEUR,
                KitsManager::KIT_ANGES => PlayerCooldowns::COOLDOWN_KIT_ANGES,
                KitsManager::KIT_DIABLOTINS => PlayerCooldowns::COOLDOWN_KIT_DIABLOTINS,
                KitsManager::KIT_ARCHANGES => PlayerCooldowns::COOLDOWN_KIT_ARCHANGES,
                KitsManager::KIT_PERSEPHONE => PlayerCooldowns::COOLDOWN_KIT_PERSEPHONE,
                KitsManager::KIT_POSEIDON => PlayerCooldowns::COOLDOWN_KIT_POSEIDON,
                KitsManager::KIT_HECATE => PlayerCooldowns::COOLDOWN_KIT_HECATE,
                KitsManager::KIT_ZEUS => PlayerCooldowns::COOLDOWN_KIT_ZEUS,
                KitsManager::KIT_HADES => PlayerCooldowns::COOLDOWN_KIT_HADES,
            };

            if (!$player->getCooldowns()->hasCooldown($kitCooldown)) {

                KitsManager::getInstance()->givePlayerKit($player, $data);
                $player->getCooldowns()->setCooldown($kitCooldown, KitsManager::getInstance()->getKitCooldown($data));
                $player->sendMessage(ConfigManager::getInstance()->getNested("messages.kit"));
            }else{

                $player->sendMessage(str_replace(
                    "{time}",
                    Utils::durationToString($player->getCooldowns()->getCooldown($kitCooldown)),
                    ConfigManager::getInstance()->getNested("messages.kit-in-cooldown"))
                );
            }

            return true;
        });

        $form->setTitle("§6Kit");

        $form->addButton("§fRefill");
        $form->addButton("§fHourly");
        $form->addButton("§fJournalier");
        $form->addButton("§fQuotidien");
        $form->addButton("§fArcher");
        $form->addButton("§fJoueur");
        $form->addButton("§iAnges");
        $form->addButton("§cDiablotins");
        $form->addButton("§9Archanges");
        $form->addButton("§5Perséphone");
        $form->addButton("§1Poseidon");
        $form->addButton("§0Hécate");
        $form->addButton("§eZeus");
        $form->addButton("§4Hadès");

        $player->sendForm($form);
    }
}