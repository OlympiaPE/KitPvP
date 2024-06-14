<?php

namespace Olympia\Kitpvp\menu\forms;

use Olympia\Kitpvp\entities\Session;

abstract class Form
{
    abstract public static function sendBaseMenu(Session $player, ...$infos): void;
}