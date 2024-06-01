<?php

namespace Olympia\Kitpvp\traits;

use muqsit\invmenu\InvMenuHandler;
use Olympia\Kitpvp\commands\admin\BoxCommand;
use Olympia\Kitpvp\commands\admin\ChestrefillCommand;
use Olympia\Kitpvp\commands\admin\ForceClearLagCommand;
use Olympia\Kitpvp\commands\admin\GivekeyCommand;
use Olympia\Kitpvp\commands\admin\NPCCommand;
use Olympia\Kitpvp\commands\admin\StartkothCommand;
use Olympia\Kitpvp\commands\admin\StopCommand;
use Olympia\Kitpvp\commands\gameplay\AreneCommand;
use Olympia\Kitpvp\commands\gameplay\DuelCommand;
use Olympia\Kitpvp\commands\gameplay\KitCommand;
use Olympia\Kitpvp\commands\gameplay\TournamentCommand;
use Olympia\Kitpvp\commands\moderation\AliasCommand;
use Olympia\Kitpvp\commands\moderation\BanCommand;
use Olympia\Kitpvp\commands\moderation\ChatCommand;
use Olympia\Kitpvp\commands\moderation\FreezeCommand;
use Olympia\Kitpvp\commands\moderation\KickCommand;
use Olympia\Kitpvp\commands\moderation\MuteCommand;
use Olympia\Kitpvp\commands\moderation\RtpCommand;
use Olympia\Kitpvp\commands\moderation\UnbanCommand;
use Olympia\Kitpvp\commands\moderation\UnfreezeCommand;
use Olympia\Kitpvp\commands\moderation\UnmuteCommand;
use Olympia\Kitpvp\commands\money\AddmoneyCommand;
use Olympia\Kitpvp\commands\money\MymoneyCommand;
use Olympia\Kitpvp\commands\money\PayCommand;
use Olympia\Kitpvp\commands\money\RemovemoneyCommand;
use Olympia\Kitpvp\commands\money\SeemoneyCommand;
use Olympia\Kitpvp\commands\money\TopmoneyCommand;
use Olympia\Kitpvp\commands\navigation\LobbyCommand;
use Olympia\Kitpvp\commands\navigation\ServeurCommand;
use Olympia\Kitpvp\commands\navigation\SpawnCommand;
use Olympia\Kitpvp\commands\stats\StatsCommand;
use Olympia\Kitpvp\commands\stats\TopdeathCommand;
use Olympia\Kitpvp\commands\stats\TopkillCommand;
use Olympia\Kitpvp\commands\stats\TopkillstreakCommand;
use Olympia\Kitpvp\commands\stats\TopnerdCommand;
use Olympia\Kitpvp\commands\utils\CosmeticCommand;
use Olympia\Kitpvp\commands\utils\DiscordCommand;
use Olympia\Kitpvp\commands\utils\EnchantmentCommand;
use Olympia\Kitpvp\commands\utils\HdvCommand;
use Olympia\Kitpvp\commands\utils\ListCommand;
use Olympia\Kitpvp\commands\utils\NightVisionCommand;
use Olympia\Kitpvp\commands\utils\ReportCommand;
use Olympia\Kitpvp\commands\utils\SettingsCommand;
use Olympia\Kitpvp\commands\utils\ShopCommand;
use Olympia\Kitpvp\commands\utils\StuffCommand;
use Olympia\Kitpvp\commands\utils\VoteCommand;
use Olympia\Kitpvp\Core;
use Olympia\Kitpvp\listeners\block\BlockBreakEvent;
use Olympia\Kitpvp\listeners\block\BlockPlaceEvent;
use Olympia\Kitpvp\listeners\entity\EntityDamageByEntityEvent;
use Olympia\Kitpvp\listeners\entity\EntityDamageEvent;
use Olympia\Kitpvp\listeners\entity\EntityShootBowEvent;
use Olympia\Kitpvp\listeners\inventory\InventoryTransactionEvent;
use Olympia\Kitpvp\listeners\player\PlayerChangeSkinEvent;
use Olympia\Kitpvp\listeners\player\PlayerChatEvent;
use Olympia\Kitpvp\listeners\player\PlayerCreationEvent;
use Olympia\Kitpvp\listeners\player\PlayerDeathEvent;
use Olympia\Kitpvp\listeners\player\PlayerExhaustEvent;
use Olympia\Kitpvp\listeners\player\PlayerItemConsumeEvent;
use Olympia\Kitpvp\listeners\player\PlayerItemUseEvent;
use Olympia\Kitpvp\listeners\player\PlayerJoinEvent;
use Olympia\Kitpvp\listeners\player\PlayerMoveEvent;
use Olympia\Kitpvp\listeners\player\PlayerPreLoginEvent;
use Olympia\Kitpvp\listeners\player\PlayerQuitEvent;
use Olympia\Kitpvp\listeners\player\PlayerRespawnEvent;
use Olympia\Kitpvp\listeners\server\CommandEvent;
use Olympia\Kitpvp\listeners\server\DataPacketReceiveEvent;
use Olympia\Kitpvp\listeners\server\DataPacketSendEvent;
use Olympia\Kitpvp\managers\ManageLoader;
use Olympia\Kitpvp\managers\types\BoxsManager;
use Olympia\Kitpvp\managers\types\ClearlagManager;
use Olympia\Kitpvp\managers\types\CombatManager;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\CosmeticsManager;
use Olympia\Kitpvp\managers\types\CPSManager;
use Olympia\Kitpvp\managers\types\DuelManager;
use Olympia\Kitpvp\managers\types\EntitiesManager;
use Olympia\Kitpvp\managers\types\EventsManager;
use Olympia\Kitpvp\managers\types\FloatingTextManager;
use Olympia\Kitpvp\managers\types\HdvManager;
use Olympia\Kitpvp\managers\types\KitsManager;
use Olympia\Kitpvp\managers\types\ModerationManager;
use Olympia\Kitpvp\managers\types\MoneyManager;
use Olympia\Kitpvp\managers\types\NPCManager;
use Olympia\Kitpvp\managers\types\ScoreboardManager;
use Olympia\Kitpvp\managers\types\ShopManager;
use Olympia\Kitpvp\managers\types\StatsManager;
use Olympia\Kitpvp\managers\types\TournamentManager;
use Olympia\Kitpvp\managers\types\VoteManager;
use Olympia\Kitpvp\managers\types\WebhookManager;
use Olympia\Kitpvp\tasks\BroadcastMessagesTask;
use Olympia\Kitpvp\tasks\ChestsRefillTask;
use Olympia\Kitpvp\tasks\CombatTask;
use Olympia\Kitpvp\tasks\DisplayCPSTask;
use Olympia\Kitpvp\tasks\ExpireHdvItemsTask;
use Olympia\Kitpvp\tasks\ScoreboardTask;
use Olympia\Kitpvp\tasks\StartKothTask;
use Olympia\Kitpvp\tasks\UpdatePlayersStats;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\Server;
use ReflectionClass;

trait LoaderTrait
{
    /** @var ManageLoader[] $managers */
    private array $managers;

    public function loadAll(Core $plugin): void
    {
        if(!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($plugin);
        }

        $this->loadPermissions();
        $this->loadManagers();
        $this->loadCommands($plugin);
        $this->loadEvents($plugin);
        $this->loadTasks($plugin);
    }

    private function loadManagers(): void
    {
        $this->managers = [
            ConfigManager::getInstance(),
            CosmeticsManager::getInstance(),
            EntitiesManager::getInstance(),
            FloatingTextManager::getInstance(),
            BoxsManager::getInstance(),
            MoneyManager::getInstance(),
            StatsManager::getInstance(),
            ClearlagManager::getInstance(),
            HdvManager::getInstance(),
            VoteManager::getInstance(),
            WebhookManager::getInstance(),
            ScoreboardManager::getInstance(),
            CPSManager::getInstance(),
            CombatManager::getInstance(),
            ShopManager::getInstance(),
            ModerationManager::getInstance(),
            KitsManager::getInstance(),
            NPCManager::getInstance(),
            DuelManager::getInstance(),
            EventsManager::getInstance(),
            TournamentManager::getInstance(),
        ];
    }

    private function loadCommands(Core $plugin): void
    {
        $commandMap = $plugin->getServer()->getCommandMap();

        $commands = [
            'ban',
            'unban',
            'kick',
            'me',
            'say',
            'list',
            'stop',
        ];

        foreach ($commands as $command) {
            $cmd = $commandMap->getCommand($command);
            if($cmd) {
                $commandMap->unregister($cmd);
            }
        }

        $commands = [
            new StopCommand(),
            new AreneCommand(),
            new CosmeticCommand(),
            new BoxCommand(),
            new GivekeyCommand(),
            new DiscordCommand(),
            new AddmoneyCommand(),
            new MymoneyCommand(),
            new PayCommand(),
            new RemovemoneyCommand(),
            new SeemoneyCommand(),
            new TopmoneyCommand(),
            new ListCommand(),
            new StuffCommand(),
            new NightVisionCommand(),
            new SpawnCommand(),
            new LobbyCommand(),
            new ServeurCommand(),
            new TopkillCommand(),
            new TopdeathCommand(),
            new TopkillstreakCommand(),
            new TopnerdCommand(),
            new StatsCommand(),
            new ForceClearLagCommand(),
            new HdvCommand(),
            new VoteCommand(),
            new EnchantmentCommand(),
            new SettingsCommand(),
            new ShopCommand(),
            new AliasCommand(),
            new BanCommand(),
            new FreezeCommand(),
            new KickCommand(),
            new MuteCommand(),
            new RtpCommand(),
            new UnbanCommand(),
            new UnfreezeCommand(),
            new UnmuteCommand(),
            new ReportCommand(),
            new ChatCommand(),
            new KitCommand(),
            new NPCCommand(),
            new DuelCommand(),
            new StartkothCommand(),
            new TournamentCommand(),
            new ChestrefillCommand(),
        ];

        foreach ($commands as $command) {
            $commandMap->register($command->getName(), $command);
        }
    }

    private function loadEvents(Core $plugin): void
    {
        $events = [
            new PlayerJoinEvent(),
            new PlayerQuitEvent(),
            new PlayerCreationEvent(),
            new PlayerDeathEvent(),
            new PlayerChangeSkinEvent(),
            new PlayerExhaustEvent(),
            new PlayerItemConsumeEvent(),
            new PlayerItemUseEvent(),
            new PlayerChatEvent(),
            new PlayerPreLoginEvent(),
            new PlayerMoveEvent(),
            new PlayerRespawnEvent(),
            new EntityDamageEvent(),
            new EntityDamageByEntityEvent(),
            new EntityShootBowEvent(),
            new DataPacketSendEvent(),
            new DataPacketReceiveEvent(),
            new CommandEvent(),
            new InventoryTransactionEvent(),
            new BlockPlaceEvent(),
            new BlockBreakEvent(),
        ];

        $eventManager = $plugin->getServer()->getPluginManager();

        foreach ($events as $event) {

            $eventManager->registerEvents($event, $plugin);
        }
    }

    private function loadTasks(Core $plugin): void
    {
        $scheduler = $plugin->getScheduler();
        $periods = ConfigManager::getInstance()->get("update-periods");

        $scheduler->scheduleRepeatingTask(new ScoreboardTask(), $periods["scoreboard"]);
        $scheduler->scheduleRepeatingTask(new DisplayCPSTask(), $periods["cps"]);
        $scheduler->scheduleRepeatingTask(new CombatTask(), $periods["combat"]);
        $scheduler->scheduleRepeatingTask(new ExpireHdvItemsTask(), $periods["expire-hdv-items"]);
        $scheduler->scheduleRepeatingTask(new StartKothTask(), $periods["start-koth"]);

        $scheduler->scheduleDelayedRepeatingTask(new UpdatePlayersStats(), $periods["player-stats"], $periods["player-stats"]);
        $scheduler->scheduleDelayedRepeatingTask(new BroadcastMessagesTask(), $periods["broadcast-message"], $periods["broadcast-message"]);
        $scheduler->scheduleDelayedRepeatingTask(new ChestsRefillTask(), 20*60*10, 20*60*10);
    }

    private function loadPermissions(): void
    {
        $permissionsReflectionClass = new ReflectionClass(Permissions::class);
        $permissionManager = PermissionManager::getInstance();

        foreach ($permissionsReflectionClass->getConstants() as $permissionName) {

            $rootOperator = $permissionManager->getPermission(DefaultPermissions::ROOT_OPERATOR);
            $permission = new Permission($permissionName, "Olympia Kitpvp permission");
            DefaultPermissions::registerPermission($permission, [$rootOperator]);
        }
    }

    public function loadWorlds(Core $plugin): void
    {
        foreach (array_diff(scandir($plugin->getServer()->getDataPath() . "worlds"), ["..", "."]) as $worldName) {

            $plugin->getServer()->getWorldManager()->loadWorld($worldName);

            $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);

            if(is_null($world)) {

                $plugin->getLogger()->alert("Veuillez supprimer le fichier $worldName dans le dossier worlds/");
            }else{

                if ($world->getFolderName() !== $world->getDisplayName()) {
                    $world->setDisplayName($world->getFolderName());
                }

                $world->setTime(6000);
                $world->stopTime();
            }
        }
    }

    public function unloadManagers(): void
    {
        foreach ($this->managers as $manager) {
            $manager->onDisable();
        }
    }
}