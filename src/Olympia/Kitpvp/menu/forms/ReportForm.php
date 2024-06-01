<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\libs\Vecnavium\FormsUI\CustomForm;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\ModerationManager;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\player\Player;

class ReportForm extends Form
{
    public static function sendBaseMenu(OlympiaPlayer $player, ...$infos): void
    {
        $form = new CustomForm(function (OlympiaPlayer $player, array $data = null) {

            if($data !== null) {

                if ($data[1] !== "") {

                    $reported = ModerationManager::getInstance()->getPlayerReportList($player->getName())[$data[0]];
                    $reason = $data[1];

                    WebhookManager::getInstance()->sendMessage("Report de {$player->getName()}", "**Joueur** : $reported\n**Raison** : $reason", WebhookManager::CHANNEL_REPORT);

                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.report"));
                }else{
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.report-reason-empty"));
                }
            }

            ModerationManager::getInstance()->removePlayerReportList($player->getName());

            return true;
        });

        $basePlayer = $player;

        $playerNames = array_values(array_map(function(Player $player): string {
            return $player->getName();
        }, array_filter($player->getServer()->getOnlinePlayers(), function(Player $player) use ($basePlayer) : bool{
            return !($basePlayer instanceof Player) || $basePlayer->canSee($player);
        })));
        unset($playerNames[array_search($basePlayer->getName(), $playerNames)]);

        ModerationManager::getInstance()->setPlayerReportList($basePlayer->getName(), $playerNames);

        $form->setTitle("§6§lReport");

        $form->addDropdown("§7Joueur", $playerNames); // data 0
        $form->addInput("§7Raison", "Cheat (killaura, reach, fly)"); // data 1

        $player->sendForm($form);
    }
}