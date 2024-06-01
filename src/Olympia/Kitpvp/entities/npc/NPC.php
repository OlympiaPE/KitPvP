<?php

namespace Olympia\Kitpvp\entities\npc;

use JsonException;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\NPCManager;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector2;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\player\Player;
use pocketmine\Server;

class NPC extends Human
{
    private array $npcInfos;

    /**
     * @throws JsonException
     */
    public function __construct(Location $location = null, CompoundTag $nbt = null, array $infos = [])
    {
        if(empty($infos) && ($nbt->equals(CompoundTag::create()) || is_null($nbt))) {

            parent::__construct($location, Human::parseSkinNBT($nbt), $nbt);
        }elseif(!is_null($nbt)) {

            $npcNbt = $nbt->getCompoundTag("NPC");

            $commands = [];
            /** @var StringTag $command */
            foreach ($npcNbt->getCompoundTag("infos")->getCompoundTag("commands")->getValue() as $command) {

                $commands[] = $command->getValue();
            }

            $this->npcInfos = [
                "id" => $npcNbt->getInt("id"),
                "name" => $npcNbt->getCompoundTag("infos")->getString("name"),
                "commands" => $commands,
            ];

            parent::__construct($location, Human::parseSkinNBT($nbt), $nbt);
        }else{

            $skinData = call_user_func_array('pack', array_merge(array("C*"), $infos["skin"]));
            $skin = new Skin("NPC" . $infos["id"], $skinData);

            unset($infos["skin"]);
            $this->npcInfos = $infos;

            parent::__construct($location, $skin, CompoundTag::create());
        }
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();

        $commandsNbt = CompoundTag::create();

        foreach ($this->npcInfos["commands"] as $command) {

            $commandsNbt->setString(uniqid("command-"), $command);
        }

        $npcNbt = CompoundTag::create()
            ->setInt("id", $this->getNpcId())
            ->setTag("infos", CompoundTag::create()
                ->setString("name", $this->npcInfos["name"])
                ->setTag("commands", $commandsNbt)
            );

        $nbt->setTag("NPC", $npcNbt);

        return $nbt;
    }

    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->setNameTag($this->npcInfos["name"]);
        $this->setNameTagVisible();
        NPCManager::getInstance()->loadNpc($this);
    }

    public function getNpcId(): int
    {
        return $this->npcInfos["id"];
    }

    public function addNpcCommand(string $command): void
    {
        $this->npcInfos["commands"][] = $command;
    }

    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();

        if($source instanceof EntityDamageByEntityEvent) {

            $damager = $source->getDamager();
            if($damager instanceof Player) {

                $damagerName = $damager->getName();
                $manager = NPCManager::getInstance();

                if($manager->getPlayerWantId($damagerName)) {

                    $id = $this->npcInfos["id"];
                    $damager->sendMessage(str_replace(
                        "{id}",
                        $id,
                        ConfigManager::getInstance()->getNested("messages.npc-get-id")
                    ));
                    $manager->removePlayerWantId($damagerName);
                }else{

                    foreach ($this->npcInfos["commands"] as $command) {
                        Server::getInstance()->dispatchCommand($damager, $command);
                    }
                }
            }
        }
    }

    protected function entityBaseTick(int $tickDiff = 1): bool
    {
        foreach ($this->getViewers() as $player) {

            $playerPos = $player->getPosition();
            $npcPos = $this->getPosition();

            $xdiff = $playerPos->x - $npcPos->x;
            $zdiff = $playerPos->z - $npcPos->z;
            $angle = atan2($zdiff, $xdiff);
            $yaw = (($angle * 180) / M_PI) - 90;
            $ydiff = $playerPos->y - $npcPos->y;
            $v = new Vector2($npcPos->x, $npcPos->z);
            $dist = $v->distance(new Vector2($playerPos->x, $playerPos->y));
            $angle = atan2($dist, $ydiff);
            $pitch = (($angle * 180) / M_PI) - 90;

            $pk = new MovePlayerPacket();
            $pk->actorRuntimeId = $this->getId();
            $pk->position = $this->getPosition()->asVector3()->add(0, $this->getEyeHeight(), 0);
            $pk->yaw = $yaw;
            $pk->pitch = $pitch;
            $pk->headYaw = $yaw;
            $pk->onGround = $this->onGround;

            $player->getNetworkSession()->sendDataPacket($pk);
        }

        return parent::entityBaseTick($tickDiff);
    }
}