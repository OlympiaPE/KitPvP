<?php

namespace Olympia\Kitpvp\handlers\types;

use Olympia\Kitpvp\handlers\Handler;

final class CPSHandler extends Handler
{
    private array $clicksData = [];

    public function onLoad(): void
    {
    }

    public function add(string $name): void
    {
        $this->clicksData[$name][] = microtime(true);
        $playerClicks = $this->clicksData[$name] ?? [];
        array_unshift($playerClicks, microtime(true));
        if (count($playerClicks) >= 100) {
            array_pop($playerClicks);
        }
    }

    public function get(string $name): float
    {
        $deltaTime = 1;
        $roundPrecision = 10;
        $playerClicks = $this->clicksData[$name] ?? [];
        if (!empty($playerClicks)) {
            $ct = microtime(true);
            return round(count(array_filter(
                    $playerClicks, static function (float $t) use ($deltaTime, $ct): bool {
                    return ($ct - $t) <= $deltaTime;
                }
                )) / $deltaTime, $roundPrecision);
        } else {
            return 0.0;
        }
    }

    public function getAll(): array
    {
        return $this->clicksData;
    }
}