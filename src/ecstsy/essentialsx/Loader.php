<?php

namespace ecstsy\essentialsx;

use ecstsy\essentialsx\Commands\AnvilCommand;
use ecstsy\essentialsx\Commands\BalanceCommand;
use ecstsy\essentialsx\Commands\BanCommand;
use ecstsy\essentialsx\Commands\BanLookupCommand;
use ecstsy\essentialsx\Commands\CreateHomeCommand;
use ecstsy\essentialsx\Commands\CreateWarpCommand;
use ecstsy\essentialsx\Commands\EcoCommand;
use ecstsy\essentialsx\Commands\ExpCommand;
use ecstsy\essentialsx\Commands\FeedCommand;
use ecstsy\essentialsx\Commands\FlyCommand;
use ecstsy\essentialsx\Commands\GamemodeCommand;
use ecstsy\essentialsx\Commands\GiveCommand;
use ecstsy\essentialsx\Commands\HealCommand;
use ecstsy\essentialsx\Commands\HomeCommand;
use ecstsy\essentialsx\Commands\HomesCommand;
use ecstsy\essentialsx\Commands\IPBanCommand;
use ecstsy\essentialsx\Commands\ItemDBCommand;
use ecstsy\essentialsx\Commands\KitCommand;
use ecstsy\essentialsx\Commands\ListWarpsCommand;
use ecstsy\essentialsx\Commands\NearCommand;
use ecstsy\essentialsx\Commands\NickCommand;
use ecstsy\essentialsx\Commands\RemoveHomeCommand;
use ecstsy\essentialsx\Commands\RemoveWarpCommand;
use ecstsy\essentialsx\Commands\SpawnCommand;
use ecstsy\essentialsx\Commands\WarpCommand;
use ecstsy\essentialsx\Commands\WorkbenchCommand;
use ecstsy\essentialsx\Listeners\EventListener;
use ecstsy\essentialsx\Player\Homes\HomeManager;
use ecstsy\essentialsx\Player\PlayerManager;
use ecstsy\essentialsx\Server\Warps\WarpManager;
use ecstsy\essentialsx\Utils\AnvilInvMenuType;
use ecstsy\essentialsx\Utils\Queries;
use ecstsy\essentialsx\Utils\Utils;
use ecstsy\essentialsx\Utils\CraftingTableInvMenuType;
use ecstsy\EssentialsX\Utils\LanguageManager;
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\session\Session;
use IvanCraft623\RankSystem\tag\Tag;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Loader extends PluginBase {
    use SingletonTrait;

    public static DataConnector $connector;

    public static PlayerManager $playerManager;
    
    public static HomeManager $homeManager;

    public static WarpManager $warpManager;

    private LanguageManager $languageManager;

    public function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        $language = $this->getConfig()->get("language", "messages-eng");
        $this->languageManager = new LanguageManager($this, $language);
        
        $files = ["config.yml", "kits.yml"];

        foreach ($files as $file) {
            $this->saveResource($file);
        }

        $subDirectories = ["locale"];

        foreach ($subDirectories as $directory) {
            $this->saveAllFilesInDirectory($directory);
        }

        foreach ($this->getResources() as $resource) {
            Utils::checkConfigVersion($resource);
        }

        $unregisteredCommands = ["ban", "ban-ip", "gamemode", "give"];

        foreach ($unregisteredCommands as $command) {
            $this->getServer()->getCommandMap()->unregister(Server::getInstance()->getCommandMap()->getCommand($command));
        }

        $commands = [
            new BanCommand($this, "ban", "Add a player to the banlist."),
            new IPBanCommand($this, "ban-ip", "Add an IP to the banlist.", ["ipban"]),
            new BanLookupCommand($this, "banlookup", "Lookup a player in the banlist."),
            new ExpCommand($this, "exp", "View your experience", ["xp", "experience"]),
            new HealCommand($this, "heal", "Restore your health"),
            new FeedCommand($this, "feed", "Restore your hunger"),
            new KitCommand($this, "kit", "View server kits", ["kits"]),
            new NearCommand($this, "near", "View nearby players within a specific radius"),
            new SpawnCommand($this, "spawn", "Teleports you the spawn of the world you're in"),
            new ItemDBCommand($this, "itemdb", "View the information of the item in hand"),
            new GamemodeCommand($this, "gamemode", "Change your gamemode", ["gm"]),
            new FlyCommand($this, "fly", "Allows the player to fly"),
            new HomesCommand($this, "homes", "View your homes"),
            new HomeCommand($this, "home", "Teleport to your saved homes"),
            new CreateHomeCommand($this, "createhome", "Create a new home", ["sethome"]),
            new RemoveHomeCommand($this, "removehome", "Remove a home", ["delhome"]),
            new CreateWarpCommand($this, "createwarp", "Create a new warp", ["setwarp"]),
            new RemoveWarpCommand($this, "removewarp", "Remove a warp", ["delwarp"]),
            new ListWarpsCommand($this, "listwarps", "List all warps", ["warps"]),
            new WarpCommand($this, "warp", "Teleport to a warp"),
            new NickCommand($this, "nick", "Change your nickname", ["nickname"]),
            new GiveCommand($this, "give", "Give an item to a player", ["i", "item"]),
            new WorkbenchCommand($this, "workbench", "Open your workbench", ["craft"]),
            new AnvilCommand($this, "anvil", "Open your anvil", ["anvil"]),
            new BalanceCommand($this, "balance", "View your balance", ["bal"]),
            new EcoCommand($this, "eco", "Manages the server economoy", ["eco"]),
        ];

        foreach ($commands as $command) {
            if (!in_array($command->getName(), $this->getConfig()->getNested("disabled-commands", []))) {
                $this->getServer()->getCommandMap()->register("Essentialsx", $command);
            }
        }
        
        $listeners = [new EventListener()];

        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }

        self::$connector = libasynql::create($this, ["type" => "sqlite", "sqlite" => ["file" => "sqlite.sql"], "worker-limit" => 2], ["sqlite" => "sqlite.sql"]);
        self::$connector->executeGeneric(Queries::PLAYERS_INIT);
        self::$connector->executeGeneric(Queries::HOMES_INIT);
        self::$connector->executeGeneric(Queries::WARPS_INIT);
        self::$connector->waitAll();

        self::$playerManager = new PlayerManager($this);
        self::$homeManager = new HomeManager($this, $this->getConfig()->get("max-homes", 3));
        self::$warpManager = new WarpManager($this);

        if ($this->getServer()->getPluginManager()->getPlugin("RankSystem") !== null) {
            $rankSystem = RankSystem::getInstance();
            $tagManager = $rankSystem->getTagManager();
            
            $tagManager->registerTag(new Tag("display_name", static function(Session $session) : string {
                return $session->getPlayer()->getDisplayName();
            }));
            $this->getLogger()->info("RankSystem found. Registering tags for this plugin.");
        } else {
            $this->getLogger()->warning("RankSystem plugin not found. The tags for this plugin will not be registered.");
        }

        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }

        $menus = [Utils::MENU_TYPE_WORKBENCH => new CraftingTableInvMenuType(), Utils::MENU_TYPE_ANVIL => new AnvilInvMenuType()];
        
        foreach ($menus as $type => $menu) {
            InvMenuHandler::getTypeRegistry()->register($type, $menu);
        }
        
    }

    private function saveAllFilesInDirectory(string $directory): void {
        $resourcePath = $this->getFile() . "resources/$directory/";
        if (!is_dir($resourcePath)) {
            $this->getLogger()->warning("Directory $directory does not exist.");
            return;
        }

        $files = scandir($resourcePath);
        if ($files === false) {
            $this->getLogger()->warning("Failed to read directory $directory.");
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $this->saveResource("$directory/$file");
        }
    }

    public function getLang(): LanguageManager {
        return $this->languageManager;
    }

    public function onDisable(): void {
        if (isset(self::$connector)) {
            self::$connector->close();
        }
    }

    public static function getDatabase(): DataConnector {
        return self::$connector;
    }

    public static function getPlayerManager(): PlayerManager {
        return self::$playerManager;
    }

    public static function getHomeManager(): HomeManager {
        return self::$homeManager;
    }

    public static function getWarpManager(): WarpManager {
        return self::$warpManager;
    }
}