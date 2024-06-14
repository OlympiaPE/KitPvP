<?php

namespace Olympia\Kitpvp\handlers\types;

use Olympia\Kitpvp\entities\objects\FloatingText;
use Olympia\Kitpvp\handlers\Handler;
use Olympia\Kitpvp\koth\Koth;
use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\Utils;
use pocketmine\scheduler\ClosureTask;

final class KothHandler extends Handler
{
    private ?Koth $koth = null;
    private ?int $kothLastCaptureTime = null;
    private ?string $kothFloatingTextId = null;

    public function onLoad(): void
    {
        $this->setRequireSaveOnDisable(true);

        $kothData = Managers::DATABASE()->getServerData("koth");
        if ($kothData["started"]) {
            Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(fn() => $this->createKoth()), 20);
        }else{
            $this->kothLastCaptureTime = $kothData["last-capture-time"] ?: time();
        }

        $this->createKothFloatingText();
    }

    public function save(): void
    {
        Managers::DATABASE()->setNestedServerData("koth.started", !is_null($this->koth));
        Managers::DATABASE()->setNestedServerData("koth.last-capture-time", $this->kothLastCaptureTime);
    }

    public function createKoth(): void
    {
        $this->koth = new Koth();
        $this->kothLastCaptureTime = null;
    }

    public function removeKoth(): void
    {
        $this->koth = null;
        $this->kothLastCaptureTime = time();
    }

    public function createKothFloatingText(): void
    {
        $this->kothFloatingTextId = Managers::FLOATING_TEXT()->createFloatingText(
            Managers::FLOATING_TEXT()->getLocationByCoordinates(
                Managers::CONFIG()->getNested("koth.floating-text.x"),
                Managers::CONFIG()->getNested("koth.floating-text.y"),
                Managers::CONFIG()->getNested("koth.floating-text.z")
            ),
            "§6KOTH",
            function (FloatingText $entity) {

                $remainingSeconds = 120 * 60 - (time() - $this->kothLastCaptureTime);
                $remainingTime = Utils::durationToString($remainingSeconds);
                $entity->setNameTag("§6KOTH\n§fDans §e$remainingTime");
            },
            20,
            $this
        );
    }

    public function removeKothFloatingText(): void
    {
        if (!is_null($this->kothFloatingTextId)) {
            Managers::FLOATING_TEXT()->removeFloatingText($this->kothFloatingTextId);
        }
    }

    public function getKothLastCaptureTime(): ?int
    {
        return $this->kothLastCaptureTime;
    }

    public function hasCurrentKoth(): bool
    {
        return !is_null($this->koth);
    }
}