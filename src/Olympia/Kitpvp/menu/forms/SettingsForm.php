<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\Vecnavium\FormsUI\CustomForm;

class SettingsForm extends Form
{
    public static function sendBaseMenu(Session $player, ...$infos): void
    {
        $defaultSettings = $player->getSettings();

        $form = new CustomForm(function (Session $player, array $data = null) use ($defaultSettings) {

            if($data !== null) {

                $newSettings = $defaultSettings;

                if ($data[0] !== $defaultSettings["kill-message"]) {
                    $newSettings["kill-message"] = $data[0];
                }

                if ($data[1] !== $defaultSettings["cps"]) {
                    $newSettings["cps"] = $data[1];
                }

                if ($data[2] !== $defaultSettings["scoreboard"]) {
                    $newSettings["scoreboard"] = $data[2];
                    if ($data[2]) {
                        Managers::SCOREBOARD()->addPlayerToDisplay($player);
                    }else{
                        Managers::SCOREBOARD()->removePlayerToDisplay($player);
                        Managers::SCOREBOARD()->remove($player);
                    }
                }

                $player->setSettings($newSettings);
                $player->sendMessage(Managers::CONFIG()->getNested("messages.update-settings"));
            }

            return true;
        });

        $form->setTitle("§6§lParamètres");

        $form->addToggle("§7Afficher les messages de mort", $defaultSettings["kill-message"]);
        $form->addToggle("§7Afficher les cps", $defaultSettings["cps"]);
        $form->addToggle("§7Afficher le scoreboard", $defaultSettings["scoreboard"]);

        $player->sendForm($form);
    }
}