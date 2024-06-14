<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\Vecnavium\FormsUI\SimpleForm;

class CosmeticForm extends Form
{
    public static function sendBaseMenu(Session $player, ...$infos): void
    {
        $form = new SimpleForm(function (Session $player, int $data = null) {

            if ($data !== null) {

                self::sendCategoryCosmeticsMenu($player, Managers::COSMETICS()->getCategoriesName()[$data]);
            }

            return true;
        });

        $form->setTitle("Cosmétiques");
        $form->setContent("§6» §fSélectionnez une catégorie");

        foreach (Managers::COSMETICS()->getCategoriesName() as $category) {

            $form->addButton($category);
        }

        $form->addButton("§cQuitter");

        $player->sendForm($form);
    }

    private static function sendCategoryCosmeticsMenu(Session $player, string $category): void
    {
        $form = new SimpleForm(function (Session $player, int $data = null) use ($category) {

            $manager = Managers::COSMETICS();

            if ($data === null || $data === count($manager->getCategoryCosmetics($category))) {

                self::sendBaseMenu($player);
            }else{

                $cosmetic = Managers::COSMETICS()->getCategoryCosmetics($category)[$data];

                if ($player->hasCosmeticByCategory($category, $cosmetic)) {

                    $cosmeticType = Managers::COSMETICS()->getCategoryCosmeticInfos($category, $cosmetic)["type"];

                    if($player->hasCosmeticEquipped($cosmeticType, $cosmetic)) {

                        // UNEQUIP
                        Managers::COSMETICS()->removePlayerCosmetic($player, $cosmeticType);
                        $player->removeCosmeticEquipped($cosmeticType);
                        $player->sendMessage(Managers::CONFIG()->getNested("messages.unequip-cosmetic"));
                    }else{

                        // EQUIP
                        Managers::COSMETICS()->removePlayerCosmetic($player, $cosmeticType);
                        Managers::COSMETICS()->applyPlayerCosmetic($player, $category, $cosmetic, $cosmeticType);
                        $player->setCosmeticEquipped($cosmeticType, $category, $cosmetic);
                        $player->sendMessage(Managers::CONFIG()->getNested("messages.equip-cosmetic"));
                    }
                }else{

                    // UNAVAILABLE
                    $player->sendMessage(Managers::CONFIG()->getNested("messages.cosmetic-unavailable"));
                }
            }

            return true;
        });

        $form->setTitle("Cosmétiques");
        $form->setContent("§6» §f$category");

        foreach (Managers::COSMETICS()->getCategoryCosmetics($category) as $cosmetic) {
            $cosmeticInfos = Managers::COSMETICS()->getCategoryCosmeticInfos($category, $cosmetic);
            $cosmeticDisplayName = $cosmeticInfos["displayName"];
            $cosmeticType = $cosmeticInfos["type"];
            $label = $player->hasCosmeticByCategory($category, $cosmetic)
                ? ($player->hasCosmeticEquipped($cosmeticType, $cosmetic)
                    ? "§7Equipé"
                    : "§aEquiper"
                )
                : "§cIndisponible";
            $form->addButton($cosmeticDisplayName . "§r\n" . $label);
        }

        $form->addButton("§cRetour");

        $player->sendForm($form);
    }
}