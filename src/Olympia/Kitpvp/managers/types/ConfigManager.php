<?php

namespace Olympia\Kitpvp\managers\types;

use Exception;
use Olympia\Kitpvp\Core;
use Olympia\Kitpvp\managers\ManageLoader;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;

final class ConfigManager extends ManageLoader
{
    use SingletonTrait;

    private array $configCache;

    private array $nestedCache = [];

    public function onInit(): void
    {
        $plugin = Core::getInstance();
        $this->configCache = $plugin->getConfig()->getAll();
    }

    public function getAll(): array
    {
        return $this->configCache;
    }

    public function get(string $key, mixed $default = false): mixed
    {
        return $this->configCache[$key] ?? $default;
    }

    public function getNested(string $key, mixed $default = null): mixed
    {
        if(isset($this->nestedCache[$key])) {
            return $this->nestedCache[$key];
        }

        $vars = explode(".", $key);
        $base = array_shift($vars);
        if(isset($this->configCache[$base])) {
            $base = $this->configCache[$base];
        }else{
            return $default;
        }

        while(count($vars) > 0) {
            $baseKey = array_shift($vars);
            if(is_array($base) && isset($base[$baseKey])) {
                $base = $base[$baseKey];
            }else{
                return $default;
            }
        }

        return $this->nestedCache[$key] = $base;
    }

    public function set(string $key, mixed $value = true): void
    {
        $this->configCache[$key] = $value;
        foreach(Utils::stringifyKeys($this->nestedCache) as $nestedKey => $nvalue){
            if(substr($nestedKey, 0, strlen($key) + 1) === ($key . ".")){
                unset($this->nestedCache[$nestedKey]);
            }
        }
    }

    public function setNested(string $key, mixed $value) : void
    {
        $vars = explode(".", $key);
        $base = array_shift($vars);

        if(!isset($this->configCache[$base])){
            $this->configCache[$base] = [];
        }

        $base = &$this->configCache[$base];

        while(count($vars) > 0){
            $baseKey = array_shift($vars);
            if(!isset($base[$baseKey])){
                $base[$baseKey] = [];
            }
            $base = &$base[$baseKey];
        }

        $base = $value;
        $this->nestedCache = [];
    }

    public function save(): void
    {
        try {

            $config = Core::getInstance()->getConfig();
            $config->setAll($this->getAll());
            $config->save();
        }catch (Exception $exception) {

            $this->getOwningPlugin()->getLogger()->alert("Erreur lors de la sauvegarde de la config : {$exception->getMessage()}");
        }
    }
}