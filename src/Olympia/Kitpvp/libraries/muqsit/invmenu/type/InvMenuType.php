<?php

declare(strict_types=1);

namespace Olympia\Kitpvp\libraries\muqsit\invmenu\type;

use Olympia\Kitpvp\libraries\muqsit\invmenu\InvMenu;
use Olympia\Kitpvp\libraries\muqsit\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType{

	public function createGraphic(InvMenu $menu, Player $player) : ?InvMenuGraphic;

	public function createInventory() : Inventory;
}