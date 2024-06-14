<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Manager;
use pocketmine\utils\Config;

final class ShopManager extends Manager
{
    public array $shop = [];

    public function onLoad(): void
    {
        Loader::getInstance()->saveResource("shop.yml");
        $shopConfig = new Config(Loader::getInstance()->getDataFolder() . "shop.yml", Config::YAML);
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