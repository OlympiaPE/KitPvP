<?php

namespace Olympia\Kitpvp;

use Exception;
use Olympia\Kitpvp\traits\LoaderTrait;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use DateTime;
use DateTimeZone;

class Core extends PluginBase
{
    use SingletonTrait, LoaderTrait;

    private bool $running;

    protected function onLoad(): void
    {
        self::setInstance($this);
        $this->setRunning();
        $this->getLogger()->info("§bChargement du core...");
        $this->loadWorlds($this);
    }

    /**
     * @throws Exception
     */
    protected function onEnable(): void
    {
        $this->loadAll($this);

        $dt = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $hour = $dt->format("H:i");
        $day = $dt->format("d/m/Y");
        $this->getLogger()->info("--> Core chargé à $hour le $day");
        $this->getLogger()->info("Core développé par RemBog (contacter rembogxv sur discord si il y a un problème)");
    }

    public function setRunning(bool $running = true): void
    {
        $this->running = $running;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }
}