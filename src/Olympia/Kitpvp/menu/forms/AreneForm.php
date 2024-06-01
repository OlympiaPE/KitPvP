<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\libs\Vecnavium\FormsUI\SimpleForm;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\Server;
use pocketmine\world\Position;

class AreneForm extends Form
{
    public static function sendBaseMenu(OlympiaPlayer $player, ...$infos): void
    {
        $form = new SimpleForm(function (OlympiaPlayer $player, int $data = null) {

            if ($data === null)
                return true;

            $configManager = ConfigManager::getInstance();

            if($data === 1) {

                $deviceOS = $player->getPlayerInfo()->getExtraData()["DeviceOS"];
                if($deviceOS !== DeviceOS::ANDROID && $deviceOS !== DeviceOS::IOS && $deviceOS !== DeviceOS::WINDOWS_PHONE) {
                    $player->sendMessage($configManager->getNested("messages.not-mobile-player"));
                    return true;
                }
            }

            $server = Server::getInstance();
            $arene = $data === 0 ? "global" : "pe";
            $worldName = $configManager->getNested("arene.$arene.world");
            $world = $server->getWorldManager()->getWorldByName($worldName);

            if(is_null($world)) {

                $player->sendMessage($configManager->getNested("messages.player-encounters-error"));
                $server->getLogger()->alert("Le monde $worldName n'existe pas, veuillez le créer.");
            }else{

                $x = $configManager->getNested("arene.$arene.spawn.x");
                $y = $configManager->getNested("arene.$arene.spawn.y");
                $z = $configManager->getNested("arene.$arene.spawn.z");
                $player->teleport(new Position($x, $y, $z, $world));
                $player->sendMessage($configManager->getNested("messages.entering-arena"));
            }

            return true;
        });

        $form->setTitle("§6Arene");

        $form->addButton("Arène\n§8Pour tout le monde");
        $form->addButton("Arène (PE)\n§8Uniquement pour les joueurs mobile");

        $player->sendForm($form);
    }
}