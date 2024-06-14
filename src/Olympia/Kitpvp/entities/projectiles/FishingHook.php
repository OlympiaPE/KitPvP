<?php

namespace Olympia\Kitpvp\entities\projectiles;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\utils\Random;

class FishingHook extends Projectile
{
    public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $shootingEntity, $nbt);

        if($shootingEntity instanceof Session) {

            $this->setMotion($shootingEntity->getDirectionVector()->multiply(Managers::CONFIG()->getNested("rod.throw-power")));
            $this->handleHookCasting($this->getMotion()->getX(), $this->getMotion()->getY(), $this->getMotion()->getZ());

        } else {
            $this->flagForDespawn();
        }
    }

    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        $damage = $this->getResultDamage();

        if($this->getOwningEntity() !== null) {
            $event = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);

            if(!$event->isCancelled()) {
                $entityHit->attack($event);
            }
        }

        $this->isCollided = true;
        $this->flagForDespawn();
    }

    protected function entityBaseTick(int $tickDiff = 1): bool
    {
        $hasUpdate = parent::entityBaseTick($tickDiff);
        $player = $this->getOwningEntity();
        $despawn = false;

        if($player instanceof Session) {
            if(
                (
                    $player->getInventory()->getItemInHand()->getTypeId() !== ItemTypeIds::FISHING_ROD &&
                    Managers::CONFIG()->getNested("rod.unequip-despawn")
                ) ||
                !$player->isAlive() ||
                $player->isClosed() ||
                $player->getLocation()->getWorld()->getFolderName() !== $this->getLocation()->getWorld()->getFolderName()
            ) {
                $despawn = true;
            }
        } else {
            $despawn = true;
        }

        if($despawn) {
            $this->flagForDespawn();
            $hasUpdate = true;
        }

        return $hasUpdate;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        $hitbox = Managers::CONFIG()->getNested("rod.hitbox");
        return new EntitySizeInfo($hitbox, $hitbox);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0;
    }

    protected function getInitialGravity(): float
    {
        return 0.1;
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::FISHING_HOOK;
    }

    public function getBaseDamage(): float
    {
        return Managers::CONFIG()->getNested("rod.damage");
    }

    private function handleHookCasting(float $x, float $y, float $z): void
    {
        $f2 = 1.0;
        $f1 = 1.5;

        $rand = new Random();
        $f = sqrt($x * $x + $y * $y + $z * $z);
        $x = $x / $f;
        $y = $y / $f;
        $z = $z / $f;
        $x = $x + $rand->nextSignedFloat() * 0.007499999832361937;
        $y = $y + $rand->nextSignedFloat() * 0.007499999832361937 * $f2;
        $z = $z + $rand->nextSignedFloat() * 0.007499999832361937 * $f2;
        $x = $x * 1.5;
        $y = $y * $f1;
        $z = $z * $f1;

        $this->motion->x += $x;
        $this->motion->y += $y;
        $this->motion->z += $z;
    }
}