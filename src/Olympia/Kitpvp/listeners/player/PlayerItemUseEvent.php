<?php

namespace Olympia\Kitpvp\listeners\player;

use Olympia\Kitpvp\entities\projectiles\FishingHook;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\TournamentManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\player\PlayerCooldowns;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent as Event;
use pocketmine\item\ItemTypeIds;

class PlayerItemUseEvent implements Listener
{
    public function onItemUse(Event $event): void
    {
        /** @var OlympiaPlayer $player */
        $player = $event->getPlayer();
        $item = $event->getItem();

        switch ($item->getTypeId())
        {
            case ItemTypeIds::ENDER_PEARL:

                if(!$player->getCooldowns()->hasCooldown(PlayerCooldowns::COOLDOWN_ENDERPEARL)) {
                    $duration = (int)ConfigManager::getInstance()->getNested("cooldowns.ender-pearl.duration");
                    $player->getCooldowns()->setCooldown(
                        PlayerCooldowns::COOLDOWN_ENDERPEARL,
                        $duration,
                        ConfigManager::getInstance()->getNested("cooldowns.ender-pearl.start-message"),
                        ConfigManager::getInstance()->getNested("cooldowns.ender-pearl.end-message")
                    );
                }else{
                    $event->cancel();
                    $duration = $player->getCooldowns()->getCooldown(PlayerCooldowns::COOLDOWN_ENDERPEARL);
                    $player->sendMessage(str_replace(
                        "{time}",
                        $duration,
                        ConfigManager::getInstance()->getNested("cooldowns.ender-pearl.message")
                    ));
                }
                break;

            case ItemTypeIds::SLIMEBALL:

                $soupFinalCount = $item->getCount();
                $heal = 4;
                $toHeal = 0;

                for($h = $player->getHealth(); $h < $player->getMaxHealth(); $h += $heal) {

                    if($soupFinalCount > 0) {

                        $toHeal += $heal;
                        $soupFinalCount--;
                        $item->setCount($soupFinalCount);
                    } else break;
                }

                if ($toHeal > 0) {

                    $player->getInventory()->setItemInHand($item);
                    $player->heal(new EntityRegainHealthEvent($player, $toHeal, EntityRegainHealthEvent::CAUSE_MAGIC));
                }
                break;

            case ItemTypeIds::NETHER_STAR:

                if ($player->inTournament()) {

                    $tournament = TournamentManager::getInstance()->getTournament();
                    $tournament->removePlayer($player);
                }
                break;

            case ItemTypeIds::FISHING_ROD:

                $location = $player->getLocation();
                $hook = new FishingHook(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player);
                $hook->spawnToAll();
                break;

            case ItemTypeIds::BOW:

                if (!$player->canFight()) {
                    $event->cancel();
                }
                break;
        }
    }
}