<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\Vecnavium\FormsUI\SimpleForm;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\Utils;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;

class StatsForm extends Form
{
    use SingletonTrait;

    public static function sendBaseMenu(Session $player, ...$infos): void
    {
        $targetName = $infos[0];
        $online = $infos[1];

        $form = new SimpleForm(function (Player $player, int $data = null) {
            return true;
        });

        $form->setTitle("§6§lSTATS");

        if($online) {
            /** @var Session $target */
            $target = Server::getInstance()->getPlayerExact($targetName);
            $deaths = $target->getDeath();
            $kills = $target->getKill();
            $killstreak = $target->getKillstreak();
            $money = $target->getMoney();
            $playingTime = Utils::durationToString($player->getPlayingTime());
        }else{
            $uuid = Managers::DATABASE()->getUuidByUsername($targetName);
            $stats = Managers::STATS()->getPlayerStat($targetName);
            $deaths = $stats["death"];
            $kills = $stats["kill"];
            $killstreak = $stats["killstreak"];
            $money = Managers::DATABASE()->getUuidData($uuid, "money");
            $playingTime = Utils::durationToString($stats["playing-time"]);
        }

        $form->setContent("§6»§f Statistique du joueur §6$targetName\n§f- Morts: §6$deaths\n§f- Kills: §6$kills\n§f- Killstreak: §6$killstreak\n§f- Argent: §6$money\n§f- Temps de connexion: §6$playingTime");

        $player->sendForm($form);
    }
}