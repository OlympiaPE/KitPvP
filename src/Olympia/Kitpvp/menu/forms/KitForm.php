<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\entities\SessionCooldowns;
use Olympia\Kitpvp\libraries\Vecnavium\FormsUI\SimpleForm;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\KitsManager;
use Olympia\Kitpvp\utils\Utils;

class KitForm extends Form
{
    public static function sendBaseMenu(Session $player, ...$infos): void
    {
        $form = new SimpleForm(function (Session $player, int $data = null) {

            if ($data === null)
                return true;

            $kitCooldown = match ($data) {
                KitsManager::KIT_REFILL => SessionCooldowns::COOLDOWN_KIT_REFILL,
                KitsManager::KIT_HOURLY => SessionCooldowns::COOLDOWN_KIT_HOURLY,
                KitsManager::KIT_DAILY => SessionCooldowns::COOLDOWN_KIT_DAILY,
                KitsManager::KIT_WEEKLY => SessionCooldowns::COOLDOWN_KIT_WEEKLY,
                KitsManager::KIT_ARCHER => SessionCooldowns::COOLDOWN_KIT_ARCHER,
                KitsManager::KIT_JOUEUR => SessionCooldowns::COOLDOWN_KIT_JOUEUR,
                KitsManager::KIT_ANGES => SessionCooldowns::COOLDOWN_KIT_ANGES,
                KitsManager::KIT_DIABLOTINS => SessionCooldowns::COOLDOWN_KIT_DIABLOTINS,
                KitsManager::KIT_ARCHANGES => SessionCooldowns::COOLDOWN_KIT_ARCHANGES,
                KitsManager::KIT_PERSEPHONE => SessionCooldowns::COOLDOWN_KIT_PERSEPHONE,
                KitsManager::KIT_POSEIDON => SessionCooldowns::COOLDOWN_KIT_POSEIDON,
                KitsManager::KIT_HECATE => SessionCooldowns::COOLDOWN_KIT_HECATE,
                KitsManager::KIT_ZEUS => SessionCooldowns::COOLDOWN_KIT_ZEUS,
                KitsManager::KIT_HADES => SessionCooldowns::COOLDOWN_KIT_HADES,
            };

            if (!$player->getCooldowns()->hasCooldown($kitCooldown)) {

                Managers::KITS()->givePlayerKit($player, $data);
                $player->getCooldowns()->setCooldown($kitCooldown, Managers::KITS()->getKitCooldown($data));
                $player->sendMessage(Managers::CONFIG()->getNested("messages.kit"));
            }else{

                $player->sendMessage(str_replace(
                    "{time}",
                    Utils::durationToString($player->getCooldowns()->getCooldown($kitCooldown)),
                    Managers::CONFIG()->getNested("messages.kit-in-cooldown"))
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