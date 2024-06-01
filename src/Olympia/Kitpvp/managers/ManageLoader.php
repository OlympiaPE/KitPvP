<?php

namespace Olympia\Kitpvp\managers;

use Olympia\Kitpvp\Core;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\utils\TextFormat;

abstract class ManageLoader implements PluginOwned, ManagerExecutor
{
    use PluginOwnedTrait;

    private bool $load = false;

    public function __construct()
    {
        $plugin = Core::getInstance();
        $this->owningPlugin = $plugin;
        $this->load();
        $this->onInit();
    }

    public function load(): void
    {
        $this->owningPlugin->getLogger()->info(TextFormat::GREEN . get_class($this) . " have loaded");
        $this->load = true;
    }

    abstract public function onInit(): void;

    public function onDisable(): void
    {
        $this->unload();
    }

    /**
     * @return Plugin
     */
    public function getOwningPlugin(): Plugin
    {
        return $this->owningPlugin;
    }

    public function isLoad(): bool
    {
        return $this->load;
    }

    public function unload(): void
    {
        $this->owningPlugin->getLogger()->info(TextFormat::RED . get_class($this) . " have unloaded");
        $this->load = false;
    }
}