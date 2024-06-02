<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\Vecnavium\FormsUI\SimpleForm;

class ServeurForm extends Form
{
    public static function sendBaseMenu(Session $player, ...$infos): void
    {
        $servers = Managers::CONFIG()->get("servers");

        $form = new SimpleForm(function (Session $player, int $data = null) use ($servers) {

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