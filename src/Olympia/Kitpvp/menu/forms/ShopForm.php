<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\libs\Vecnavium\FormsUI\CustomForm;
use Olympia\Kitpvp\libs\Vecnavium\FormsUI\SimpleForm;
use Olympia\Kitpvp\managers\types\BoxsManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\ShopManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use pocketmine\item\StringToItemParser;

class ShopForm extends Form
{
    public static function sendBaseMenu(OlympiaPlayer $player, ...$infos): void
    {
        $stage = $infos[0] ?? "base";

        $form = new SimpleForm(function (OlympiaPlayer $player, int $data = null) use ($stage) {

            if($data === null) {

                return true;
            }elseif($data === count(ShopManager::getInstance()->getButtons($stage)) && $stage !== "base") {

                self::sendBaseMenu($player, str_contains($stage, ":")
                        ? implode(':', array_slice(explode(':', $stage), 0, -1))
                        : "base"
                );
            }else{

                if($stage === "base") {

                    self::sendBaseMenu($player, array_keys(ShopManager::getInstance()->shop)[$data]);
                }else{
                    $name = array_keys(ShopManager::getInstance()->getNested($stage))[$data + 1];
                    $shop = ShopManager::getInstance()->getNested("$stage:" . $name);

                    if($shop["type"] === "item" || $shop["type"] === "block") {

                        self::sendBuyForm($player, $name, $shop);
                    }elseif($shop["type"] === "category") {

                        self::sendBaseMenu($player, "$stage:$name");
                    }
                }
            }

            return true;
        });

        $form->setTitle("§6§lSHOP");

        foreach (ShopManager::getInstance()->getButtons($stage) as $button => $texture) {

            if(!is_null($texture)) {
                $form->addButton($button, 0, $texture);
            }else{
                $form->addButton($button);
            }
        }

        if($stage !== "base")
            $form->addButton("§cRetour");

        $player->sendForm($form);
    }

    private static function sendBuyForm(OlympiaPlayer $player, string $itemName, array $properties): void
    {
        $item = StringToItemParser::getInstance()->parse($properties["item"]);

        if(is_null($item)) {
            $item = match ($properties["item"]) {
                "key_vote" => BoxsManager::getInstance()->getKeyItem(BoxsManager::BOX_VOTE),
                "key_epic" => BoxsManager::getInstance()->getKeyItem(BoxsManager::BOX_EPIC),
                "key_event" => BoxsManager::getInstance()->getKeyItem(BoxsManager::BOX_EVENT),
                "key_shop" => BoxsManager::getInstance()->getKeyItem(BoxsManager::BOX_SHOP),
                "key_cosmetic" => BoxsManager::getInstance()->getKeyItem(BoxsManager::BOX_COSMETIC),
            };
        }

        $form = new CustomForm(function (OlympiaPlayer $player, array $data = null) use ($itemName, $properties, $item) {

            if(!is_null($data)) {

                $count = $data[1];
                if (!is_numeric($count) || $count < 1 || str_contains($count, '.')) {
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.invalid-amount"));
                    return true;
                }
                $price = (int)$properties["price"] * $count;
                if ($player->hasEnoughMoney($price)) {

                    if(!is_null($item)) {

                        $item->setCount($count);

                        if ($player->getInventory()->canAddItem($item)) {

                            $player->removeMoney($price);
                            $player->getInventory()->addItem($item);
                            $player->sendMessage(str_replace(
                                ["{count}", "{item}", "{price}"],
                                [$count, $itemName, $price],
                                ConfigManager::getInstance()->getNested("messages.shop-buy-item")
                            ));
                        } else {
                            $player->sendMessage(ConfigManager::getInstance()->getNested("messages.no-room-in-inventory"));
                        }
                    }
                } else {
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.not-enough-money"));
                }
            }

            return true;
        });

        $form->setTitle("§6§lSHOP");
        $form->addLabel("§7Combien de §6$itemName §7souhaitez-vous acheter ?"); #data 0

        $form->addInput("§6Quantité", 64); #data 1

        $player->sendForm($form);
    }
}