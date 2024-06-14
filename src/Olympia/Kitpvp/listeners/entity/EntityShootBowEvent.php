<?php

namespace Olympia\Kitpvp\listeners\entity;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\libraries\SenseiTarzan\ExtraEvent\Class\EventAttribute;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityShootBowEvent as Event;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;

class EntityShootBowEvent implements Listener
{
    #[EventAttribute(EventPriority::NORMAL)]
    public function onShootBow(Event $event): void
    {
       $entity = $event->getEntity();

       if ($entity instanceof Session) {

           $spawnArrowInfos = Managers::CONFIG()->getNested("bow.spawn-arrow");

           $location = $entity->getLocation();
           $directionVector = $entity->getDirectionVector()->normalize()->divide($spawnArrowInfos["distance"]);
           $y = $entity->getEyePos()->getY() - $spawnArrowInfos["y"];
           $position = $location->subtract($directionVector->getX(), $y, $directionVector->getZ());

           $diff = $entity->getItemUseDuration();
           $p = $diff / Managers::CONFIG()->getNested("bow.arrow-power");
           $baseForce = min((($p ** 2) + $p * 2) / 3, 1);

           $arrow = new Arrow(Location::fromObject(
               $position,
               $entity->getWorld(),
               ($location->yaw > 180 ? 360 : 0) - $location->yaw,
               -$location->pitch
           ), $entity, $baseForce >= 1, null);

           /** @var Arrow $projectile */
           $projectile = $event->getProjectile();
           $arrow->setPunchKnockback($projectile->getPunchKnockback());
           $arrow->setMotion($entity->getDirectionVector());

           $event->setProjectile($arrow);
       }
    }
}