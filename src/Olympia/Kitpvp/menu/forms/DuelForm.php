<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\duel\DuelStates;
use Olympia\Kitpvp\libs\Vecnavium\FormsUI\CustomForm;
use Olympia\Kitpvp\libs\Vecnavium\FormsUI\SimpleForm;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\DuelManager;
use Olympia\Kitpvp\player\OlympiaPlayer;

class DuelForm extends Form
{
    public static function sendBaseMenu(OlympiaPlayer $player, ...$infos): void
    {
        $form = new SimpleForm(function (OlympiaPlayer $player, int $data = null) {

            if ($data !== null) {

                switch ($data) {

                    case 0:
                        self::sendCreateDuelMenu($player);
                        break;

                    case 1:
                        self::sendAcceptDuelMenu($player);
                        break;

                    case 2:
                        self::sendSpectateDuelMenu($player);
                        break;
                }
            }
            return true;
        });

        $form->setTitle("§6Duel");

        $form->addButton("Créer un duel"); // data 0
        $form->addButton("Accepter un duel"); // data 1
        $form->addButton("Regarder un duel"); // data 2

        $player->sendForm($form);
    }

    private static function sendCreateDuelMenu(OlympiaPlayer $player): void
    {
        $form = new CustomForm(function (OlympiaPlayer $player, array $data = null) {

            if($data !== null) {

                $targetName = $data[0];
                $mise = $data[1] ?? 0;
                $type = $data[2];

                if (
                    !empty($player->getInventory()->getContents()) ||
                    !empty($player->getArmorInventory()->getContents()) ||
                    !empty($player->getOffHandInventory()->getContents())
                ) {
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.inventory-must-be-empty"));
                    return true;
                }

                /** @var $target OlympiaPlayer|null */
                if (is_null($target = $player->getServer()->getPlayerByPrefix($targetName))) {
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.player-not-found"));
                    return true;
                }

                if ($target->getName() === $player->getName()) {
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.duel-with-yourself"));
                    return true;
                }

                if (!is_numeric($mise)) {
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.invalid-amount"));
                    return true;
                }

                if (!$player->hasEnoughMoney($mise)) {
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.duel-player-too-high-mise"));
                    return true;
                }

                if (!$target->hasEnoughMoney($mise)) {
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.duel-target-too-high-mise"));
                    return true;
                }

                DuelManager::getInstance()->createDuel($player, $target, (int)$mise, $type);

                $player->sendMessage(str_replace(
                    "{player}",
                    $target->getName(),
                    ConfigManager::getInstance()->getNested("messages.duel-player-create"))
                );

                $target->sendMessage(str_replace(
                    ["{player}", "{mise}"],
                    [$player->getName(), $mise],
                    ConfigManager::getInstance()->getNested("messages.duel-target-create"))
                );
            }else{
                self::sendBaseMenu($player);
            }
            return true;
        });

        $form->setTitle("§6Duel");

        $form->addInput("Joueur", $player->getDisplayName()); // data 0
        $form->addInput("Mise (optionnel)", "", "0"); // data 1
        $form->addDropdown("Type", DuelManager::getInstance()->getAllDuelTypes()); // data 2

        $player->sendForm($form);
    }

    private static function sendAcceptDuelMenu(OlympiaPlayer $player): void
    {
        $duels = DuelManager::getInstance()->getPlayerDuels($player);

        $form = new SimpleForm(function (OlympiaPlayer $player, int $data = null) use ($duels) {

            if ($data !== null && $data !== count($duels)) {

                $duel = $duels[$data];
                $duel->start();

            }else{
                self::sendBaseMenu($player);
            }
            return true;
        });

        $form->setTitle("§6Duel");

        foreach ($duels as $duel) {
            $players = implode(" vs ", $duel->getPlayersName());
            $mise = $duel->getMise();
            $type = DuelManager::getInstance()->getDuelTypeDisplayName($duel->getType());
            $form->addButton("§6$players\n§fMise $mise$ §6- §f$type");
        }

        $form->addButton("§cRetour");

        $player->sendForm($form);
    }

    private static function sendSpectateDuelMenu(OlympiaPlayer $player): void
    {
        $duels = DuelManager::getInstance()->getDuelsByStates([DuelStates::STARTING, DuelStates::IN_PROGRESS]);

        $form = new SimpleForm(function (OlympiaPlayer $player, int $data = null) use ($duels) {

            if ($data !== null && $data !== count($duels)) {

                $duel = $duels[$data];
                $duel->addSpectator($player);

            }else{
                self::sendBaseMenu($player);
            }
            return true;
        });

        $form->setTitle("§6Duel");

        foreach ($duels as $duel) {
            $players = implode(" vs ", $duel->getPlayersName());
            $mise = $duel->getMise();
            $type = DuelManager::getInstance()->getDuelTypeDisplayName($duel->getType());
            $form->addButton("§6$players\n§fMise $mise$ §6- §f$type");
        }

        $form->addButton("§cRetour");

        $player->sendForm($form);
    }
}