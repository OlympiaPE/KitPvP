<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\Vecnavium\FormsUI\CustomForm;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\WebhookManager;
use pocketmine\player\Player;

class ReportForm extends Form
{
    public static function sendBaseMenu(Session $player, ...$infos): void
    {
        $form = new CustomForm(function (Session $player, array $data = null) {

            if($data !== null) {

                if ($data[1] !== "") {

                    $reported = Managers::MODERATION()->getPlayerReportList($player->getName())[$data[0]];
                    $reason = $data[1];

                    Managers::WEBHOOK()->sendMessage("Report de {$player->getName()}", "**Joueur** : $reported\n**Raison** : $reason", WebhookManager::CHANNEL_REPORT);

                    $player->sendMessage(Managers::CONFIG()->getNested("messages.report"));
                }else{
                    $player->sendMessage(Managers::CONFIG()->getNested("messages.report-reason-empty"));
                }
            }

            Managers::MODERATION()->removePlayerReportList($player->getName());

            return true;
        });

        $basePlayer = $player;

        $playerNames = array_values(array_map(function(Player $player): string {
            return $player->getName();
        }, array_filter($player->getServer()->getOnlinePlayers(), function(Player $player) use ($basePlayer) : bool{
            return !($basePlayer instanceof Player) || $basePlayer->canSee($player);
        })));
        unset($playerNames[array_search($basePlayer->getName(), $playerNames)]);

        Managers::MODERATION()->setPlayerReportList($basePlayer->getName(), $playerNames);

        $form->setTitle("§6§lReport");

        $form->addDropdown("§7Joueur", $playerNames); // data 0
        $form->addInput("§7Raison", "Cheat (killaura, reach, fly)"); // data 1

        $player->sendForm($form);
    }
}