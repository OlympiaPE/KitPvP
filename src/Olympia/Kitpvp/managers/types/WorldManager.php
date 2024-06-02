<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Manager;
use pocketmine\Server;

class WorldManager extends Manager
{
    /**
     * @return void
     */
    public function onLoad(): void
    {
        $plugin = Loader::getInstance();
        $server = $plugin->getServer();

        foreach (array_diff(scandir($server->getDataPath() . "worlds"), ["..", "."]) as $worldName) {

            $server->getWorldManager()->loadWorld($worldName);

            $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);

            if(is_null($world)) {

                $plugin->getLogger()->alert("Veuillez supprimer le fichier $worldName dans le dossier worlds/");
            }else{

                if ($world->getFolderName() !== $world->getDisplayName()) {
                    $world->setDisplayName($world->getFolderName());
                }

                $world->setTime(6000);
                $world->stopTime();
            }
        }
    }
}