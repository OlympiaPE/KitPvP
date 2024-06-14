<?php

namespace Olympia\Kitpvp\managers\types;

use Exception;
use Olympia\Kitpvp\entities\boxs\CosmeticBox;
use Olympia\Kitpvp\entities\boxs\EpicBox;
use Olympia\Kitpvp\entities\boxs\EventBox;
use Olympia\Kitpvp\entities\boxs\ShopBox;
use Olympia\Kitpvp\entities\boxs\VoteBox;
use Olympia\Kitpvp\entities\npc\NPC;
use Olympia\Kitpvp\entities\objects\FloatingText;
use Olympia\Kitpvp\entities\projectiles\FishingHook;
use Olympia\Kitpvp\managers\Manager;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\cache\StaticPacketCache;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

final class EntitiesManager extends Manager
{
    /**
     * @var array
     */
    private array $identifiersList = [];

    /**
     * @return void
     */
    public function onLoad(): void
    {
        $this->registerAllEntities();
    }

    /**
     * @return void
     */
    public function registerAllEntities(): void
    {
        $entityFactory = EntityFactory::getInstance();

        $entityFactory->register(FishingHook::class, function(World $world, CompoundTag $nbt): FishingHook {
            return new FishingHook(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['FishingHook', 'minecraft:fishing_hook']);

        $entityFactory->register(NPC::class, function (World $world, CompoundTag $nbt): Entity {
            return new NPC(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['NPC']);

        $entityFactory->register(FloatingText::class, function (World $world, CompoundTag $nbt): Entity {
            return new FloatingText(EntityDataHelper::parseLocation($nbt, $world));
        }, ['FloatingText']);

        $entityFactory->register(EpicBox::class, function (World $world, CompoundTag $nbt): Living {
            return new EpicBox(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['EpicBox', 'olympia:epic_box']);
        $this->registerIdList("EpicBox", "olympia:epic_box");

        $entityFactory->register(EventBox::class, function (World $world, CompoundTag $nbt): Living {
            return new EventBox(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['EventBox', 'olympia:event_box']);
        $this->registerIdList("EventBox", "olympia:event_box");

        $entityFactory->register(ShopBox::class, function (World $world, CompoundTag $nbt): Living {
            return new ShopBox(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['ShopBox', 'olympia:shop_box']);
        $this->registerIdList("ShopBox", "olympia:shop_box");

        $entityFactory->register(VoteBox::class, function (World $world, CompoundTag $nbt): Living {
            return new VoteBox(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['VoteBox', 'olympia:vote_box']);
        $this->registerIdList("VoteBox", "olympia:vote_box");

        $entityFactory->register(CosmeticBox::class, function (World $world, CompoundTag $nbt): Living {
            return new CosmeticBox(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['CosmeticBox', 'olympia:cosmetic_box']);
        $this->registerIdList("CosmeticBox", "olympia:cosmetic_box");
    }

    /**
     * @param string $name
     * @param string $id
     * @return void
     */
    public function registerIdList(string $name, string $id): void
    {
        $idList = StaticPacketCache::getInstance()->getAvailableActorIdentifiers()->identifiers->getRoot()->getListTag("idlist");
        $idList->push(CompoundTag::create()->setString("id", $id));
        $this->identifiersList[$name] = $id;
    }

    /**
     * @param string $name
     * @param Location $location
     * @return null|Living
     * @throws Exception
     */
    public function spawnEntity(string $name, Location $location): null|Living
    {
        $chunkX = $location->getFloorX() >> Chunk::COORD_BIT_SIZE;
        $chunkZ = $location->getFloorZ() >> Chunk::COORD_BIT_SIZE;

        if(!$location->getWorld()->isChunkLoaded($chunkX, $chunkZ)) {
            $location->getWorld()->loadChunk($chunkX, $chunkZ);
        }

        $entity = match($name) {
            "EpiqueBox" => new EpicBox($location, CompoundTag::create()),
            "EventBox" => new EventBox($location, CompoundTag::create()),
            "BoutiqueBox" => new ShopBox($location, CompoundTag::create()),
            "VoteBox" => new VoteBox($location, CompoundTag::create()),
            "CosmeticBox" => new CosmeticBox($location, CompoundTag::create()),
        };

        $entity->spawnToAll();
        return $entity;
    }

    /**
     * @return array
     */
    public function getIdentifierList(): array
    {
        return $this->identifiersList;
    }
}