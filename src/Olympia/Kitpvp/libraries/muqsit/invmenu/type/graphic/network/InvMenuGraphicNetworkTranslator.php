<?php

declare(strict_types=1);

namespace Olympia\Kitpvp\libraries\muqsit\invmenu\type\graphic\network;

use Olympia\Kitpvp\libraries\muqsit\invmenu\session\InvMenuInfo;
use Olympia\Kitpvp\libraries\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

interface InvMenuGraphicNetworkTranslator{

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void;
}