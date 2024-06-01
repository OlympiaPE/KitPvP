<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\libs\Vecnavium\FormsUI\SimpleForm;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\utils\Utils;
use pocketmine\item\Armor;
use pocketmine\item\Bow;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Sword;
use pocketmine\item\Tool;

class EnchantForm extends Form
{
    public const ENCHANT_PROTECTION = 0;
    public const ENCHANT_UNBREAKING = 1;
    public const ENCHANT_SHARPNESS = 2;
    public const ENCHANT_POWER = 3;
    public const ENCHANT_PUNCH = 4;

    public static function sendBaseMenu(OlympiaPlayer $player, ...$infos): void
    {
        $form = new SimpleForm(function (OlympiaPlayer $player, int $data = null) {

            if($data === null) {

                return true;
            }

            $item = $player->getInventory()->getItemInHand();

            switch ($data) {

                case self::ENCHANT_PROTECTION:

                    if(!$item instanceof Armor) {
                        $player->sendMessage(ConfigManager::getInstance()->getNested("messages.enchant-incompatible-item"));
                        return true;
                    }
                    break;

                case self::ENCHANT_UNBREAKING:

                    if(!$item instanceof Durable) {
                        $player->sendMessage(ConfigManager::getInstance()->getNested("messages.enchant-incompatible-item"));
                        return true;
                    }
                    break;

                case self::ENCHANT_SHARPNESS:

                    if(!$item instanceof Sword) {
                        $player->sendMessage(ConfigManager::getInstance()->getNested("messages.enchant-incompatible-item"));
                        return true;
                    }
                    break;

                case self::ENCHANT_POWER:
                case self::ENCHANT_PUNCH:

                    if(!$item instanceof Bow) {
                        $player->sendMessage(ConfigManager::getInstance()->getNested("messages.enchant-incompatible-item"));
                        return true;
                    }
                    break;
            }

            self::selectEnchantLevelForm($player, $data);

            return true;
        });

        $form->setTitle("Enchantement");
        $form->setContent("§6» §fSélectionnez votre enchantement.");
        $form->addButton("Protection");
        $form->addButton("Solidité");
        $form->addButton("Tranchant");
        $form->addButton("Puissance");
        $form->addButton("Punch");


        $player->sendForm($form);
    }

    public static function selectEnchantLevelForm(OlympiaPlayer $player, int $echantment): void
    {
        $enchantmentName = match ($echantment) {
            self::ENCHANT_PROTECTION => "Protection",
            self::ENCHANT_UNBREAKING => "Solidité",
            self::ENCHANT_SHARPNESS => "Tranchant",
            self::ENCHANT_POWER => "Puissance",
            self::ENCHANT_PUNCH => "Punch",
        };

        $level = [
            self::ENCHANT_PROTECTION => [3, 300],
            self::ENCHANT_UNBREAKING => [3, 750],
            self::ENCHANT_SHARPNESS => [3, 400],
            self::ENCHANT_POWER => [3, 300],
            self::ENCHANT_PUNCH => [2, 900],
        ];

        $form = new SimpleForm(function (OlympiaPlayer $player, int $data = null) use ($level, $echantment) {

            if($data === null) {

                return true;
            }

            $enchantLevel = $data + 1;
            $price = $enchantLevel * $level[$echantment][1];

            if($player->hasEnoughMoney($price)) {

                $player->removeMoney($price);
                $item = $player->getInventory()->getItemInHand();

                $enchant = match ($echantment) {
                    self::ENCHANT_PROTECTION => new EnchantmentInstance(VanillaEnchantments::PROTECTION(), $enchantLevel),
                    self::ENCHANT_UNBREAKING => new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), $enchantLevel),
                    self::ENCHANT_SHARPNESS => new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), $enchantLevel),
                    self::ENCHANT_POWER => new EnchantmentInstance(VanillaEnchantments::POWER(), $enchantLevel),
                    self::ENCHANT_PUNCH => new EnchantmentInstance(VanillaEnchantments::PUNCH(), $enchantLevel),
                };
                $item->addEnchantment($enchant);
                $player->getInventory()->setItemInHand($item);

                $player->sendMessage(str_replace(
                    "{price}",
                    (string)$price,
                    ConfigManager::getInstance()->getNested("messages.enchant-success")
                ));
            }else{
                $player->sendMessage(ConfigManager::getInstance()->getNested("messages.not-enough-money"));
            }

            return true;
        });

        $form->setTitle("Enchantement");
        $form->setContent("§6Enchant : §r$enchantmentName");

        $price = $level[$echantment][1];

        for($c = 1; $c < $level[$echantment][0] + 1; $c++) {

            $l = Utils::numberToRomanRepresentation($c);
            $form->addButton("§6$enchantmentName $l\n§f$price");
            $price += $level[$echantment][1];
        }

        $player->sendForm($form);
    }
}