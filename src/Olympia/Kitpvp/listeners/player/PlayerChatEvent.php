<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\ModerationManager;
use Olympia\Kitpvp\traits\BlacklistTrait;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent as Event;

class PlayerChatEvent implements Listener
{
    use BlacklistTrait;

    public function onChat(Event $event): void
    {
        $player = $event->getPlayer();

        if(ModerationManager::getInstance()->isMute($player->getName())) {
            $event->cancel();
            $rt = ModerationManager::getInstance()->getMuteRemainingTime($player->getName());
            $player->sendMessage(str_replace(
                "{remainingTime}",
                $rt,
                ConfigManager::getInstance()->getNested("messages.mute")
            ));
        }

        if(!$player->getServer()->isOp($player->getName())) {

            if(ModerationManager::getInstance()->isChatLocked()) {

                $player->sendMessage(ConfigManager::getInstance()->getNested("messages.chat-locked"));
                $event->cancel();
            }elseif ($this->isBlacklist($player)) {

                $player->sendMessage(ConfigManager::getInstance()->getNested("messages.anti-spam"));
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