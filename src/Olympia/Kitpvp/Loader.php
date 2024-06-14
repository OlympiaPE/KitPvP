<?php

namespace Olympia\Kitpvp;

use czechpmdevs\multiworld\libs\CortexPE\Commando\PacketHooker;
use DateTime;
use DateTimeZone;
use Exception;
use muqsit\invmenu\InvMenuHandler;
use Olympia\Kitpvp\handlers\Handlers;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase
{
    use SingletonTrait;

    private bool $running;

    protected function onLoad(): void
    {
        self::setInstance($this);
        $this->setRunning();
        $this->getLogger()->info("§bChargement du core...");
    }

    /**
     * @throws Exception
     */
    protected function onEnable(): void
    {
        if(!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
        if(!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }

        Managers::load();
        Handlers::load();

        $dt = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $hour = $dt->format("H:i");
        $day = $dt->format("d/m/Y");
        $this->getLogger()->info("§b--> Core chargé à $hour le $day");
        $this->getLogger()->info("§bCore développé par RemBog (contacter rembogbe sur discord si il y a un problème)");
    }

    protected function onDisable(): void
    {
        Handlers::save();
        Managers::save();
    }

    public function setRunning(bool $running = true): void
    {
        $this->running = $running;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function getFile(): string
    {
        return parent::getFile();
    }
}