<?php

namespace Olympia\Kitpvp\tasks;

use DateTime;
use DateTimeZone;
use Exception;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\scheduler\Task;

class ExpireHdvItemsTask extends Task
{
    /**
     * @throws Exception
     */
    public function onRun(): void
    {
        foreach (Managers::HDV()->purchasableItems as $player => $items) {
            foreach ($items as $key => $itemProperties) {
                if(!$itemProperties["expired"]) {
                    $format = "d/m/Y H:i";
                    $date = DateTime::createFromFormat($format, $itemProperties["date"], new DateTimeZone('Europe/Paris'));
                    $diff = $date->diff(new DateTime('now', new DateTimeZone('Europe/Paris')));
                    if ($diff->h >= 24 || $diff->d > 0) {
                        Managers::HDV()->setItemExpired($player, $key);
                    }
                }
            }
        }
    }
}