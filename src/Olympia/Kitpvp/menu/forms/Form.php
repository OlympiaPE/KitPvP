<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\player\OlympiaPlayer;

abstract class Form
{
    abstract public static function sendBaseMenu(OlympiaPlayer $player, ...$infos): void;
}