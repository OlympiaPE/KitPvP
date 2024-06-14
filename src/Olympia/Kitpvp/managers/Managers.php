<?php

namespace Olympia\Kitpvp\managers;

use Olympia\Kitpvp\Loader;
use Olympia\Kitpvp\managers\types\BoxsManager;
use Olympia\Kitpvp\managers\types\CombatManager;
use Olympia\Kitpvp\managers\types\CommandManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\CosmeticsManager;
use Olympia\Kitpvp\managers\types\DatabaseManager;
use Olympia\Kitpvp\managers\types\DuelManager;
use Olympia\Kitpvp\managers\types\EntitiesManager;
use Olympia\Kitpvp\managers\types\FloatingTextManager;
use Olympia\Kitpvp\managers\types\HdvManager;
use Olympia\Kitpvp\managers\types\KitsManager;
use Olympia\Kitpvp\managers\types\ListenerManager;
use Olympia\Kitpvp\managers\types\ModerationManager;
use Olympia\Kitpvp\managers\types\NPCManager;
use Olympia\Kitpvp\managers\types\PermissionManager;
use Olympia\Kitpvp\managers\types\ScoreboardManager;
use Olympia\Kitpvp\managers\types\ShopManager;
use Olympia\Kitpvp\managers\types\StatsManager;
use Olympia\Kitpvp\managers\types\TaskManager;
use Olympia\Kitpvp\managers\types\TournamentManager;
use Olympia\Kitpvp\managers\types\VoteManager;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\managers\types\WorldManager;
use pocketmine\utils\RegistryTrait;
use ReflectionClass;

/**
 * @method static WorldManager WORLD()
 * @method static ConfigManager CONFIG()
 * @method static DatabaseManager DATABASE()
 * @method static PermissionManager PERMISSION()
 * @method static StatsManager STATS()
 * @method static CosmeticsManager COSMETICS()
 * @method static FloatingTextManager FLOATING_TEXT()
 * @method static BoxsManager BOXS()
 * @method static CombatManager COMBAT()
 * @method static CommandManager COMMAND()
 * @method static DuelManager DUEL()
 * @method static EntitiesManager ENTITIES()
 * @method static HdvManager HDV()
 * @method static KitsManager KITS()
 * @method static ListenerManager LISTENER()
 * @method static ModerationManager MODERATION()
 * @method static NPCManager NPC()
 * @method static ScoreboardManager SCOREBOARD()
 * @method static ShopManager SHOP()
 * @method static TaskManager TASK()
 * @method static TournamentManager TOURNAMENT()
 * @method static VoteManager VOTE()
 * @method static WebhookManager WEBHOOK()
 */
class Managers
{
    use RegistryTrait;

    private function __construct() {}

    /**
     * @param string $name
     * @param Manager $manager
     * @return void
     */
    protected static function register(string $name, Manager $manager) : void{
        $manager->onLoad();
        self::_registryRegister($name, $manager);
        Loader::getInstance()->getLogger()->info("§aThe $name manager has been successfully registered");
    }

    /**
     * @return array
     */
    public static function getAll() : array{
        return self::_registryGetAll();
    }

    /**
     * @return void
     */
    public static function load(): void
    {
        self::checkInit();
    }

    /**
     * @return void
     */
    protected static function setup(): void
    {
        $reflectionClass = new ReflectionClass(self::class);
        $namespace = $reflectionClass->getNamespaceName();
        $docComment = $reflectionClass->getDocComment();

        $matches = [];
        preg_match_all('/@method\s+static\s+(\S+)\s+([^\s()]+)\(\)/', $docComment, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {

            $className = $match[1];
            $managerClass = "\\$namespace\\types\\$className";
            $manager = new $managerClass();

            if ($manager instanceof Manager) {
                self::register(strtolower($match[2]), $manager);
            }else{
                Loader::getInstance()->getLogger()->error("[Manager] The $className class does not inherit from Manager !");
            }
        }
    }

    /**
     * @return void
     */
    public static function save(): void
    {
        foreach (array_reverse(self::getAll()) as $manager) {
            if ($manager instanceof Manager && $manager->isRequireSaveOnDisable()) {
                $manager->save();
            }
        }
    }
}