<?php

namespace Olympia\Kitpvp\entities\objects;

use Closure;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\Server;

class FloatingText extends Entity
{
    private string $ftId;

    private ?Closure $updateClosure = null;

    private mixed $managerClass = null;

    private int $updateTime = 1;

    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setNameTagVisible();
        $this->setScale(0.0001);
        $this->setNameTagAlwaysVisible();
        $this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0);
        $this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);
        parent::initEntity($nbt);
    }

    public function setUpdateNameTagClosure(?Closure $closure): void
    {
        $this->updateClosure = $closure;
    }

    public function onUpdate(int $currentTick): bool
    {
        if(
            !is_null($this->updateClosure) &&
            Server::getInstance()->isRunning() &&
            ($currentTick % $this->updateTime * 20 === 0 || $currentTick === 1)
        ) {
            $this->updateClosure->call($this->managerClass ?? $this, $this);
        }

        return parent::onUpdate($currentTick);
    }

    public function setFtId(string $ftId): void
    {
        $this->ftId = $ftId;
    }

    public function getFtId(): string
    {
        return $this->ftId;
    }

    public function setManagerClass(mixed $class): void
    {
        $this->managerClass = $class;
    }

    public function setUpdateTime(int $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public static function getNetworkTypeId(): string
    {
        return "minecraft:armor_stand";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.01, 0.01, 0.01);
    }

    protected function getName(): string
    {
        return "FloatingText";
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0;
    }

    protected function getInitialGravity(): float
    {
        return 0;
    }

    public function isNoClientPredictions(): bool
    {
        return true;
    }
}