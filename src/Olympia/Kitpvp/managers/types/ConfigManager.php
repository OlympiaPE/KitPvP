<?php

namespace Olympia\Kitpvp\managers\types;

use Exception;
use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Manager;
use pocketmine\utils\Utils;

final class ConfigManager extends Manager
{
    private array $configCache;

    private array $nestedCache = [];

    public function onLoad(): void
    {
        $plugin = Loader::getInstance();
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

            $config = Loader::getInstance()->getConfig();
            $config->setAll($this->getAll());
            $config->save();
        }catch (Exception $exception) {

            Loader::getInstance()->getLogger()->alert("§4[Config] Error saving config : {$exception->getMessage()}");
        }
    }
}