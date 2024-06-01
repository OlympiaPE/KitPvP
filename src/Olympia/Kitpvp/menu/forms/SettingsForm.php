<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\libs\Vecnavium\FormsUI\CustomForm;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\ScoreboardManager;
use Olympia\Kitpvp\player\OlympiaPlayer;

class SettingsForm extends Form
{
    public static function sendBaseMenu(OlympiaPlayer $player, ...$infos): void
    {
        $defaultSettings = $player->getSettings();

        $form = new CustomForm(function (OlympiaPlayer $player, array $data = null) use ($defaultSettings) {

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
                        ScoreboardManager::getInstance()->addPlayerToDisplay($player);
                    }else{
                        ScoreboardManager::getInstance()->removePlayerToDisplay($player);
                        ScoreboardManager::getInstance()->remove($player);
                    }
                }

                $player->setSettings($newSettings);
                $player->sendMessage(ConfigManager::getInstance()->getNested("messages.update-settings"));
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