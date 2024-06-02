<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\traits\BlacklistTrait;
use Olympia\Kitpvp\utils\constants\Permissions;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent as Event;

class PlayerChatEvent implements Listener
{
    use BlacklistTrait;

    #[EventAttribute(EventPriority::NORMAL)]
    public function onChat(Event $event): void
    {
        $player = $event->getPlayer();

        if(Managers::MODERATION()->isMute($player->getName())) {
            $event->cancel();
            $rt = Managers::MODERATION()->getMuteRemainingTime($player->getName());
            $player->sendMessage(str_replace(
                "{remainingTime}",
                $rt,
                Managers::CONFIG()->getNested("messages.mute")
            ));
        }

        if(!$player->getServer()->isOp($player->getName())) {

            if(Managers::MODERATION()->isChatLocked()) {

                $player->sendMessage(Managers::CONFIG()->getNested("messages.chat-locked"));
                $event->cancel();
            }elseif ($this->isBlacklist($player)) {

                $player->sendMessage(Managers::CONFIG()->getNested("messages.anti-spam"));
                $event->cancel();
            }else{

                $this->blacklist($player, 2.0);

                if(!$player->hasPermission(Permissions::MESSAGE_COLORFUL)) {

                    $message = preg_replace('/§./u', '', $event->getMessage());
                    $event->setMessage($message);
                }
            }
        }
    }
}