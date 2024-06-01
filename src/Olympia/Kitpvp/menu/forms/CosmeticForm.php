<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\libs\Vecnavium\FormsUI\SimpleForm;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\CosmeticsManager;
use Olympia\Kitpvp\player\OlympiaPlayer;

class CosmeticForm extends Form
{
    public static function sendBaseMenu(OlympiaPlayer $player, ...$infos): void
    {
        $form = new SimpleForm(function (OlympiaPlayer $player, int $data = null) {

            if ($data !== null) {

                self::sendCategoryCosmeticsMenu($player, CosmeticsManager::getInstance()->getCategoriesName()[$data]);
            }

            return true;
        });

        $form->setTitle("Cosmétiques");
        $form->setContent("§6» §fSélectionnez une catégorie");

        foreach (CosmeticsManager::getInstance()->getCategoriesName() as $category) {

            $form->addButton($category);
        }

        $form->addButton("§cQuitter");

        $player->sendForm($form);
    }

    private static function sendCategoryCosmeticsMenu(OlympiaPlayer $player, string $category): void
    {
        $form = new SimpleForm(function (OlympiaPlayer $player, int $data = null) use ($category) {

            $manager = CosmeticsManager::getInstance();

            if ($data === null || $data === count($manager->getCategoryCosmetics($category))) {

                self::sendBaseMenu($player);
            }else{

                $cosmetic = CosmeticsManager::getInstance()->getCategoryCosmetics($category)[$data];

                if ($player->hasCosmeticByCategory($category, $cosmetic)) {

                    $cosmeticType = CosmeticsManager::getInstance()->getCategoryCosmeticInfos($category, $cosmetic)["type"];

                    if($player->hasCosmeticEquipped($cosmeticType, $cosmetic)) {

                        // UNEQUIP
                        CosmeticsManager::getInstance()->removePlayerCosmetic($player, $cosmeticType);
                        $player->removeCosmeticEquipped($cosmeticType);
                        $player->sendMessage(ConfigManager::getInstance()->getNested("messages.unequip-cosmetic"));
                    }else{

                        // EQUIP
                        CosmeticsManager::getInstance()->removePlayerCosmetic($player, $cosmeticType);
                        CosmeticsManager::getInstance()->applyPlayerCosmetic($player, $category, $cosmetic, $cosmeticType);
                        $player->setCosmeticEquipped($cosmeticType, $category, $cosmetic);
                        $player->sendMessage(ConfigManager::getInstance()->getNested("messages.equip-cosmetic"));
                    }
                }else{

                    // UNAVAILABLE
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.cosmetic-unavailable"));
                }
            }

            return true;
        });

        $form->setTitle("Cosmétiques");
        $form->setContent("§6» §f$category");

        foreach (CosmeticsManager::getInstance()->getCategoryCosmetics($category) as $cosmetic) {
            $cosmeticInfos = CosmeticsManager::getInstance()->getCategoryCosmeticInfos($category, $cosmetic);
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