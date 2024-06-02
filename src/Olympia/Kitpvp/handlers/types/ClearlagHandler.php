<?php

namespace Olympia\Kitpvp\handlers\types;

use Olympia\Kitpvp\handlers\Handler;
use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

final class ClearlagHandler extends Handler
{
    public function onLoad(): void
    {
        $this->startClearlag();
    }

    public function startClearlag(bool $force = false): void
    {
        $scheduler = Loader::getInstance()->getScheduler();

        if($force) {

            Server::getInstance()->broadcastMessage(Managers::CONFIG()->getNested("messages.clear-lag-forced-warning"));

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
                    Managers::CONFIG()->getNested("messages.clear-lag")
                ));
            }), 20*10);
        }else{

            $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($scheduler): void {

                Server::getInstance()->broadcastMessage(Managers::CONFIG()->getNested("messages.clear-lag-warning"));

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
                        Managers::CONFIG()->getNested("messages.clear-lag")
                    ));

                    $this->startClearlag();
                }), 20*60);
            }), 20*60*9);
        }
    }
}