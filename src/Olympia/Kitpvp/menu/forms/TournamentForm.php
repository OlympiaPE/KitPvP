<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\entities\SessionCooldowns;
use Olympia\Kitpvp\libraries\Vecnavium\FormsUI\CustomForm;
use Olympia\Kitpvp\libraries\Vecnavium\FormsUI\SimpleForm;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\TournamentManager;
use Olympia\Kitpvp\utils\constants\Permissions;
use Olympia\Kitpvp\utils\Utils;

class TournamentForm extends Form
{
    public static function sendBaseMenu(Session $player, ...$infos): void
    {
        $form = new SimpleForm(function (Session $player, int $data = null) {

            if ($data === null)
                return true;

            if ($data === 0) {

                if ($player->hasPermission(Permissions::HOST_TOURNAMENT_24) || $player->hasPermission(Permissions::HOST_TOURNAMENT_12) || $player->hasPermission(Permissions::HOST_TOURNAMENT)) {
                    if (!$player->getCooldowns()->hasCooldown(SessionCooldowns::COOLDOWN_HOST_TOURNAMENT) || $player->hasPermission(Permissions::HOST_TOURNAMENT)) {
                        if (!Managers::TOURNAMENT()->hasCurrentTournament()) {
                            self::sendCreateTournamentMenu($player);
                        }else{
                            $player->sendMessage(Managers::CONFIG()->getNested("messages.tournament-error-already-tournament"));
                        }
                    }else{
                        $player->sendMessage(str_replace(
                            "{time}",
                            Utils::durationToString($player->getCooldowns()->getCooldown(SessionCooldowns::COOLDOWN_HOST_TOURNAMENT)),
                            Managers::CONFIG()->getNested("messages.tournament-host-in-cooldown")
                        ));
                    }
                }else{
                    $player->sendMessage(Managers::CONFIG()->getNested("messages.not-allowed"));
                }
            }else{

                if (Managers::TOURNAMENT()->hasCurrentTournament()) {
                    if (Managers::TOURNAMENT()->isTournamentStarted()) {
                        $player->sendMessage(Managers::CONFIG()->getNested("messages.tournament-join-error"));
                    }else{
                        if (
                            empty($player->getInventory()->getContents()) &&
                            empty($player->getArmorInventory()->getContents()) &&
                            empty($player->getOffHandInventory()->getContents())
                        ) {
                            Managers::TOURNAMENT()->getTournament()->addPlayer($player);
                        }else{
                            $player->sendMessage(Managers::CONFIG()->getNested("messages.inventory-must-be-empty"));
                        }
                    }
                }else{
                    $player->sendMessage(Managers::CONFIG()->getNested("messages.tournament-join-error"));
                }
            }

            return true;
        });

        $tournamentState = Managers::TOURNAMENT()->hasCurrentTournament()
            ? (Managers::TOURNAMENT()->isTournamentStarted()
                ? "§cImpossible de rejoindre"
                : "§aCommence dans " . Utils::durationToShortString(Managers::TOURNAMENT()->getTournament()->getStartIn()))
            : "§cAucun tournois en cours";

        $form->setTitle("§6Tournois");

        $form->addButton("Créer un tournois");
        $form->addButton("Rejoindre le tournois\n$tournamentState");

        $player->sendForm($form);
    }

    private static function sendCreateTournamentMenu(Session $player): void
    {
        $types = ["Nodebuff", "Sumo", "Bracket"];

        $form = new CustomForm(function (Session $player, array $data = null) {

            if($data !== null) {

                if (!Managers::TOURNAMENT()->hasCurrentTournament()) {

                    $type = match ($data[0]) {
                        0 => TournamentManager::TOURNAMENT_TYPE_NODEBUFF,
                        1 => TournamentManager::TOURNAMENT_TYPE_SUMO,
                        2 => TournamentManager::TOURNAMENT_TYPE_BRACKET,
                    };

                    if (!$player->hasPermission(Permissions::HOST_TOURNAMENT)) {
                        $player->getCooldowns()->setCooldown(
                            SessionCooldowns::COOLDOWN_HOST_TOURNAMENT,
                            60 * 60 * ($player->hasPermission(Permissions::HOST_TOURNAMENT_12) ? 12 : 24)
                        );
                    }

                    $player->sendMessage(str_replace(
                        "{type}",
                        $type,
                        Managers::CONFIG()->getNested("messages.tournament-host-create")
                    ));

                    Managers::TOURNAMENT()->createTournament($player, $type);

                    if ($data[1]) {
                        if (
                            empty($player->getInventory()->getContents()) &&
                            empty($player->getArmorInventory()->getContents()) &&
                            empty($player->getOffHandInventory()->getContents())
                        ) {
                            Managers::TOURNAMENT()->getTournament()->addPlayer($player);
                        }else{
                            $player->sendMessage(Managers::CONFIG()->getNested("messages.inventory-must-be-empty"));
                        }
                    }
                }else{
                    $player->sendMessage(Managers::CONFIG()->getNested("messages.tournament-error-already-tournament"));
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