<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\libs\Vecnavium\FormsUI\CustomForm;
use Olympia\Kitpvp\libs\Vecnavium\FormsUI\SimpleForm;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\TournamentManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\player\PlayerCooldowns;
use Olympia\Kitpvp\utils\Permissions;
use Olympia\Kitpvp\utils\Utils;

class TournamentForm extends Form
{
    public static function sendBaseMenu(OlympiaPlayer $player, ...$infos): void
    {
        $form = new SimpleForm(function (OlympiaPlayer $player, int $data = null) {

            if ($data === null)
                return true;

            if ($data === 0) {

                if ($player->hasPermission(Permissions::HOST_TOURNAMENT_24) || $player->hasPermission(Permissions::HOST_TOURNAMENT_12) || $player->hasPermission(Permissions::HOST_TOURNAMENT)) {
                    if (!$player->getCooldowns()->hasCooldown(PlayerCooldowns::COOLDOWN_HOST_TOURNAMENT) || $player->hasPermission(Permissions::HOST_TOURNAMENT)) {
                        if (!TournamentManager::getInstance()->hasCurrentTournament()) {
                            self::sendCreateTournamentMenu($player);
                        }else{
                            $player->sendMessage(ConfigManager::getInstance()->getNested("messages.tournament-error-already-tournament"));
                        }
                    }else{
                        $player->sendMessage(str_replace(
                            "{time}",
                            Utils::durationToString($player->getCooldowns()->getCooldown(PlayerCooldowns::COOLDOWN_HOST_TOURNAMENT)),
                            ConfigManager::getInstance()->getNested("messages.tournament-host-in-cooldown")
                        ));
                    }
                }else{
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.not-allowed"));
                }
            }else{

                if (TournamentManager::getInstance()->hasCurrentTournament()) {
                    if (TournamentManager::getInstance()->isTournamentStarted()) {
                        $player->sendMessage(ConfigManager::getInstance()->getNested("messages.tournament-join-error"));
                    }else{
                        if (
                            empty($player->getInventory()->getContents()) &&
                            empty($player->getArmorInventory()->getContents()) &&
                            empty($player->getOffHandInventory()->getContents())
                        ) {
                            TournamentManager::getInstance()->getTournament()->addPlayer($player);
                        }else{
                            $player->sendMessage(ConfigManager::getInstance()->getNested("messages.inventory-must-be-empty"));
                        }
                    }
                }else{
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.tournament-join-error"));
                }
            }

            return true;
        });

        $tournamentState = TournamentManager::getInstance()->hasCurrentTournament()
            ? (TournamentManager::getInstance()->isTournamentStarted()
                ? "§cImpossible de rejoindre"
                : "§aCommence dans " . Utils::durationToShortString(TournamentManager::getInstance()->getTournament()->getStartIn()))
            : "§cAucun tournois en cours";

        $form->setTitle("§6Tournois");

        $form->addButton("Créer un tournois");
        $form->addButton("Rejoindre le tournois\n$tournamentState");

        $player->sendForm($form);
    }

    private static function sendCreateTournamentMenu(OlympiaPlayer $player): void
    {
        $types = ["Nodebuff", "Sumo", "Bracket"];

        $form = new CustomForm(function (OlympiaPlayer $player, array $data = null) {

            if($data !== null) {

                if (!TournamentManager::getInstance()->hasCurrentTournament()) {

                    $type = match ($data[0]) {
                        0 => TournamentManager::TOURNAMENT_TYPE_NODEBUFF,
                        1 => TournamentManager::TOURNAMENT_TYPE_SUMO,
                        2 => TournamentManager::TOURNAMENT_TYPE_BRACKET,
                    };

                    if (!$player->hasPermission(Permissions::HOST_TOURNAMENT)) {
                        $player->getCooldowns()->setCooldown(
                            PlayerCooldowns::COOLDOWN_HOST_TOURNAMENT,
                            60 * 60 * ($player->hasPermission(Permissions::HOST_TOURNAMENT_12) ? 12 : 24)
                        );
                    }

                    $player->sendMessage(str_replace(
                        "{type}",
                        $type,
                        ConfigManager::getInstance()->getNested("messages.tournament-host-create")
                    ));

                    TournamentManager::getInstance()->createTournament($player, $type);

                    if ($data[1]) {
                        if (
                            empty($player->getInventory()->getContents()) &&
                            empty($player->getArmorInventory()->getContents()) &&
                            empty($player->getOffHandInventory()->getContents())
                        ) {
                            TournamentManager::getInstance()->getTournament()->addPlayer($player);
                        }else{
                            $player->sendMessage(ConfigManager::getInstance()->getNested("messages.inventory-must-be-empty"));
                        }
                    }
                }else{
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.tournament-error-already-tournament"));
                }
            }else{
                self::sendBaseMenu($player);
            }
            return true;
        });

        $form->setTitle("§6Tournois");

        $form->addDropdown("Type", $types); // data 0
        $form->addToggle("Rejoindre", true); // data 1

        $player->sendForm($form);
    }
}