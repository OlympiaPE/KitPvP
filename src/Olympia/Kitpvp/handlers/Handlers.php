<?php

namespace Olympia\Kitpvp\handlers;

use Olympia\Kitpvp\handlers\types\ChestRefillHandler;
use Olympia\Kitpvp\handlers\types\ClearlagHandler;
use Olympia\Kitpvp\handlers\types\CPSHandler;
use Olympia\Kitpvp\handlers\types\KothHandler;
use Olympia\Kitpvp\Loader;
use pocketmine\utils\RegistryTrait;
use ReflectionClass;

/**
 * @method static ChestRefillHandler CHEST_REFILL()
 * @method static ClearlagHandler CLEARLAG()
 * @method static CPSHandler CPS()
 * @method static KothHandler KOTH()
 */
class Handlers
{
    use RegistryTrait;

    private function __construct() {}

    /**
     * @param string $name
     * @param Handler $handler
     * @return void
     */
    protected static function register(string $name, Handler $handler) : void{
        $handler->onLoad();
        self::_registryRegister($name, $handler);
        Loader::getInstance()->getLogger()->info("§eThe $name handler has been successfully registered");
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
            $handlerClass = "\\$namespace\\types\\$className";
            $handler = new $handlerClass();

            if ($handler instanceof Handler) {
                self::register(strtolower($match[2]), $handler);
            }else{
                Loader::getInstance()->getLogger()->error("[Handler] The $className class does not inherit from Handler !");
            }
        }
    }

    /**
     * @return void
     */
    public static function save(): void
    {
        foreach (self::getAll() as $handler) {
            if ($handler instanceof Handler && $handler->isRequireSaveOnDisable()) {
                $handler->save();
            }
        }
    }
}