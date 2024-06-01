<?php

namespace Olympia\Kitpvp\managers\types;

use Olympia\Kitpvp\Core;
use Olympia\Kitpvp\managers\ManageLoader;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;

final class ClearlagManager extends ManageLoader
{
    use SingletonTrait;

    public function onInit(): void
    {
        $this->startClearlag();
    }

    public function startClearlag(bool $force = false): void
    {
        $scheduler = Core::getInstance()->getScheduler();

        if($force) {

            Server::getInstance()->broadcastMessage(ConfigManager::getInstance()->getNested("messages.clear-lag-forced-warning"));

            $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($scheduler): void {

                $amount = 0;

                foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {

                    foreach ($world->getEntities() as $entity) {

                        if($entity instanceof ItemEntity || $entity instanceof ExperienceOrb) {

                            $entity->flagForDespawn();
                            $amount += $entity instanceof ItemEntity ? $entity->getItem()->getCount() : 1;
                        }
                    }
                }

                Server::getInstance()->broadcastMessage(str_replace(
                    "{amount}",
                    $amount,
                    ConfigManager::getInstance()->getNested("messages.clear-lag")
                ));
            }), 20*10);
        }else{

            $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($scheduler): void {

                Server::getInstance()->broadcastMessage(ConfigManager::getInstance()->getNested("messages.clear-lag-warning"));

                $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($scheduler): void {

                    $amount = 0;

                    foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {

                        foreach ($world->getEntities() as $entity) {

                            if($entity instanceof ItemEntity || $entity instanceof ExperienceOrb) {

                                $entity->flagForDespawn();
                                $amount += $entity instanceof ItemEntity ? $entity->getItem()->getCount() : 1;
                            }
                        }
                    }

                    Server::getInstance()->broadcastMessage(str_replace(
                        "{amount}",
                        $amount,
                        ConfigManager::getInstance()->getNested("messages.clear-lag")
                    ));

                    $this->startClearlag();
                }), 20*60);
            }), 20*60*9);
        }
    }
}