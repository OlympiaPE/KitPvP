<?php

declare(strict_types=1);

namespace Olympia\Kitpvp\libraries\muqsit\invmenu\type\graphic\network;

use Olympia\Kitpvp\libraries\muqsit\invmenu\session\InvMenuInfo;
use Olympia\Kitpvp\libraries\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;

final class ActorInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator{

	public function __construct(
		readonly private int $actor_runtime_id
	){}

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void{
		$packet->actorUniqueId = $this->actor_runtime_id;
		$packet->blockPosition = new BlockPosition(0, 0, 0);
	}
}