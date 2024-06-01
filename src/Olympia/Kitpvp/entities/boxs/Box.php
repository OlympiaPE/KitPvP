<?php

namespace Olympia\Kitpvp\entities\boxs;

use Olympia\Kitpvp\managers\types\BoxsManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

abstract class Box extends Living
{
    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->setNoClientPredictions();
        $this->setHasGravity(false);
        $this->setNameTagAlwaysVisible();
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1, 1);
    }

    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();

        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if ($damager instanceof Player) {

                $item = $damager->getInventory()->getItemInHand();
                $box = match ($this->getName()) {
                    "VoteBox" => BoxsManager::BOX_VOTE,
                    "EpiqueBox" => BoxsManager::BOX_EPIC,
                    "EventBox" => BoxsManager::BOX_EVENT,
                    "BoutiqueBox" => BoxsManager::BOX_SHOP,
                    "CosmeticBox" => BoxsManager::BOX_COSMETIC,
                };

                if (BoxsManager::getInstance()->isKey($item) && $item->getCustomName() === $this->getKey()) {

                    BoxsManager::getInstance()->useKey($damager, $box);
                }else{

                    $damager->sendMessage(str_replace(
                        "{key}",
                        BoxsManager::getInstance()->getKeyName($box),
                        ConfigManager::getInstance()->getNested("messages.no-key"))
                    );
                }
            }
        }
    }

    abstract public function getKey(): string;
}