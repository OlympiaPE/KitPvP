<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\duel\DuelStates;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\Vecnavium\FormsUI\CustomForm;
use Olympia\Kitpvp\libraries\Vecnavium\FormsUI\SimpleForm;

class DuelForm extends Form
{
    public static function sendBaseMenu(Session $player, ...$infos): void
    {
        $form = new SimpleForm(function (Session $player, int $data = null) {

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

    private static function sendCreateDuelMenu(Session $player): void
    {
        $form = new CustomForm(function (Session $player, array $data = null) {

            if($data !== null) {

                $targetName = $data[0];
                $mise = $data[1] ?? 0;
                $type = $data[2];

                if (
                    !empty($player->getInventory()->getContents()) ||
                    !empty($player->getArmorInventory()->getContents()) ||
                    !empty($player->getOffHandInventory()->getContents())
                ) {
                    $player->sendMessage(Managers::CONFIG()->getNested("messages.inventory-must-be-empty"));
                    return true;
                }

                /** @var $target Session|null */
                if (is_null($target = $player->getServer()->getPlayerByPrefix($targetName))) {
                    $player->sendMessage(Managers::CONFIG()->getNested("messages.player-not-found"));
                    return true;
                }

                if ($target->getName() === $player->getName()) {
                    $player->sendMessage(Managers::CONFIG()->getNested("messages.duel-with-yourself"));
                    return true;
                }

                if (!is_numeric($mise)) {
                    $player->sendMessage(Managers::CONFIG()->getNested("messages.invalid-amount"));
                    return true;
                }

                if (!$player->hasEnoughMoney($mise)) {
                    $player->sendMessage(Managers::CONFIG()->getNested("messages.duel-player-too-high-mise"));
                    return true;
                }

                if (!$target->hasEnoughMoney($mise)) {
                    $player->sendMessage(Managers::CONFIG()->getNested("messages.duel-target-too-high-mise"));
                    return true;
                }

                Managers::DUEL()->createDuel($player, $target, (int)$mise, $type);

                $player->sendMessage(str_replace(
                    "{player}",
                    $target->getName(),
                    Managers::CONFIG()->getNested("messages.duel-player-create"))
                );

                $target->sendMessage(str_replace(
                    ["{player}", "{mise}"],
                    [$player->getName(), $mise],
                    Managers::CONFIG()->getNested("messages.duel-target-create"))
                );
            }else{
                self::sendBaseMenu($player);
            }
            return true;
        });

        $form->setTitle("§6Duel");

        $form->addInput("Joueur", $player->getDisplayName()); // data 0
        $form->addInput("Mise (optionnel)", "", "0"); // data 1
        $form->addDropdown("Type", Managers::DUEL()->getAllDuelTypes()); // data 2

        $player->sendForm($form);
    }

    private static function sendAcceptDuelMenu(Session $player): void
    {
        $duels = Managers::DUEL()->getPlayerDuels($player);

        $form = new SimpleForm(function (Session $player, int $data = null) use ($duels) {

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
            $type = Managers::DUEL()->getDuelTypeDisplayName($duel->getType());
            $form->addButton("§6$players\n§fMise $mise$ §6- §f$type");
        }

        $form->addButton("§cRetour");

        $player->sendForm($form);
    }

    private static function sendSpectateDuelMenu(Session $player): void
    {
        $duels = Managers::DUEL()->getDuelsByStates([DuelStates::STARTING, DuelStates::IN_PROGRESS]);

        $form = new SimpleForm(function (Session $player, int $data = null) use ($duels) {

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
            $type = Managers::DUEL()->getDuelTypeDisplayName($duel->getType());
            $form->addButton("§6$players\n§fMise $mise$ §6- §f$type");
        }

        $form->addButton("§cRetour");

        $player->sendForm($form);
    }
}