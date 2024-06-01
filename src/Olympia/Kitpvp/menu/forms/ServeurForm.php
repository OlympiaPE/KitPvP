<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\libs\Vecnavium\FormsUI\SimpleForm;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\player\OlympiaPlayer;

class ServeurForm extends Form
{
    public static function sendBaseMenu(OlympiaPlayer $player, ...$infos): void
    {
        $servers = ConfigManager::getInstance()->get("servers");

        $form = new SimpleForm(function (OlympiaPlayer $player, int $data = null) use ($servers) {

            if ($data === null)
                return true;

            $server = array_keys($servers)[$data];
            $player->transfer($servers[$server]["ip"], $servers[$server]["port"]);
            return true;
        });

        $form->setTitle("§6Serveur");

        foreach ($servers as $server => $infos) {
            $form->addButton($server);
        }

        $player->sendForm($form);
    }
}