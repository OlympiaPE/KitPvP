<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\Core;
use Olympia\Kitpvp\managers\ManageLoader;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

final class ShopManager extends ManageLoader
{
    use SingletonTrait;

    private array $saleableItems = [];
    public array $shop = [];

    public function onInit(): void
    {
        Core::getInstance()->saveResource("shop.yml");
        $shopConfig = new Config($this->getOwningPlugin()->getDataFolder() . "shop.yml", Config::YAML);
        $shop = $shopConfig->getAll();

        $this->shop = $shop;
    }

    public function getButtons(string $stage): array
    {
        $buttons = [];
        if($stage === "base") {
            foreach ($this->shop as $name => $properties) {
                if($name !== "type") {
                    $buttons[$name] = null;
                }
            }
        }elseif(strpos($stage, ":")) {
            $shop = $this->getNested($stage);
            foreach ($shop as $name => $properties) {
                if($name !== "type") {
                    if(isset($properties["image"])) {
                        $buttons[$name] = $properties["image"];
                    }else{
                        $buttons[$name] = null;
                    }
                }
            }
        }else{
            foreach ($this->shop[$stage] as $name => $properties) {
                if($name !== "type") {
                    if(isset($properties["image"])) {
                        $buttons[$name] = $properties["image"];
                    }else{
                        $buttons[$name] = null;
                    }
                }
            }
        }
        return $buttons;
    }

    public function getNested(string $key): array
    {
        $stageIndex = explode(":", $key);
        $shop = $this->shop[array_shift($stageIndex)];
        while(count($stageIndex) > 0) {
            $shop = $shop[array_shift($stageIndex)];
        }
        return $shop;
    }
}