<?php

namespace ecstsy\essentialsx\Utils;

use ecstsy\essentialsx\Loader;
use muqsit\invmenu\InvMenu;
use pocketmine\color\Color;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;

class Utils {

    public const MENU_TYPE_WORKBENCH = "essentialsx:workbench";

    public const MENU_TYPE_ANVIL = "essentialsx:anvil";
    
    private static array $configCache = [];

    public static array $tpaRequests = [];
    
    public static array $tpahereRequests = [];

    public static array $lastTpaRequester = [];

    public static string $notEnoughMoney = "&r&4Error: &cYou don't have enough money!";

    public static function workBench(): InvMenu {
        return InvMenu::create(self::MENU_TYPE_WORKBENCH);
    }

    public static function anvil(): InvMenu {
        return InvMenu::create(self::MENU_TYPE_ANVIL);
    }
    
    public static function getConfiguration(PluginBase $plugin, string $fileName): Config {
        $pluginFolder = Loader::getInstance()->getDataFolder();
        $filePath = $pluginFolder . $fileName;

        if (isset(self::$configCache[$filePath])) {
            return self::$configCache[$filePath];
        }

        if (!file_exists($filePath)) {
            Loader::getInstance()->getLogger()->warning("Configuration file '$filePath' not found.");
            return null;
        }
        
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'yml':
            case 'yaml':
                $config = new Config($filePath, Config::YAML);
                break;
    
            case 'json':
                $config = new Config($filePath, Config::JSON);
                break;
    
            default:
                Loader::getInstance()->getLogger()->warning("Unsupported configuration file format for '$filePath'.");
                return null;
        }

        self::$configCache[$filePath] = $config;
        return $config;
    }

    // TODO switch to ConfigUpdater
    public static function checkConfigVersion(string $fileName): void {
        $configVersion = Loader::getInstance()->getConfig()->get("version");
        $messageVersion = Loader::getInstance()->getLang()->get("version");
        $kitsVersion = Utils::getConfiguration(Loader::getInstance(), "kits.yml")->get("version");
    
        if ($configVersion === null || $configVersion !== "1.0.2") {
            Loader::getInstance()->getLogger()->info("Updating version of $fileName");
            self::saveOldConfig("config.yml");
            Loader::getInstance()->saveDefaultConfig();
        } elseif ($messageVersion === null || $messageVersion !== "1.0.5") {
            Loader::getInstance()->getLogger()->info("Updating version of $fileName");
            self::saveOldConfig("locale/messages-eng.yml");
            Loader::getInstance()->saveResource($fileName);
        } elseif ($kitsVersion === null || $kitsVersion !== "1.0.0") {
            Loader::getInstance()->getLogger()->info("Updating version of $fileName");
            self::saveOldConfig("kits.yml");
            Loader::getInstance()->saveResource($fileName);
        }
    }

    public static function saveOldConfig(string $fileName): void {
        $dataFolder = Loader::getInstance()->getDataFolder();
        $oldConfigPath = $dataFolder . "old_" . dirname($fileName) . DIRECTORY_SEPARATOR;
        $fullOldConfigPath = $oldConfigPath . basename($fileName);
    
        if (!is_dir($oldConfigPath)) {
            mkdir($oldConfigPath, 0755, true);
        }
    
        Loader::getInstance()->saveResource($fileName, false);
    
        rename($dataFolder . $fileName, $fullOldConfigPath);
    }
    

    public static function getPermissionLockedStatus(Player $player, string $permission) : string {
        if ($player->hasPermission($permission)) {
            $text = C::RESET . C::GREEN . C::BOLD . "UNLOCKED";
        } else {
            $text = C::RESET . C::RED . C::BOLD . "LOCKED";
        }

        return $text;
    }

    public static function setupItems(array $inputData): array
    {
        $items = [];
        $stringToItemParser = StringToItemParser::getInstance();
    
        foreach ($inputData as $data) {
            $itemString = $data["item"];
            $item = $stringToItemParser->parse($itemString);
    
            if ($item === null) {
                continue;
            }
    
            $amount = $data["amount"] ?? 1;
            $item->setCount($amount);
    
            $name = $data["name"] ?? null;
            if ($name !== null) {
                $item->setCustomName(C::colorize($name));
            }
    
            $lore = $data["lore"] ?? null;
            if ($lore !== null) {
                $lore = array_map(function ($line) {
                    return C::colorize($line);
                }, $lore);
                $item->setLore($lore);
            }
    
            $enchantments = $data["enchantments"] ?? null;
            if ($enchantments !== null) {
                foreach ($enchantments as $enchantmentData) {
                    $enchantment = $enchantmentData["enchant"] ?? null;
                    $level = $enchantmentData["level"] ?? 1;
                    if ($enchantment !== null) {
                        $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse($enchantment)), $level);
                    }
                }
            }

            $color = $data["color"] ?? null;
            if ($item instanceof Armor && $color !== null) {
                $rgb = explode(",", $color);
                $item->setCustomColor(Color::fromRGB((int)$rgb[0]));
            }
    
            $nbtData = $data["nbt"] ?? null;
            if ($nbtData !== null) {
                $tag = $nbtData["tag"] ?? "";
                $value = $nbtData["value"] ?? "";
                $item->getNamedTag()->setString($tag, $value);
            }
    
            $items[] = $item;
        }
    
        return $items;
    }

    public static function matchGameMode(mixed $modeString): ?GameMode {
        $modeString = strtolower($modeString);

        $gameModes = [
            "gmc" => GameMode::CREATIVE(),
            "c" => GameMode::CREATIVE(),
            "creative" => GameMode::CREATIVE(),
            "1" => GameMode::CREATIVE(),
            "gms" => GameMode::SURVIVAL(),
            "s" => GameMode::SURVIVAL(),
            "0" => GameMode::SURVIVAL(),
            "survival" => GameMode::SURVIVAL(),
            "gma" => GameMode::ADVENTURE(),
            "2" => GameMode::ADVENTURE(),
            "a" => GameMode::ADVENTURE(),
            "adventure" => GameMode::ADVENTURE(),
            "gmsp" => GameMode::SPECTATOR(),
            "4" => GameMode::SPECTATOR(),
            "sp" => GameMode::SPECTATOR(),
            "spectator" => GameMode::SPECTATOR(),
        ];

        return $gameModes[$modeString] ?? null;
    }

    public static function toggleFlight(Player $player, bool $forceOff = false): void
    {

        $config = Loader::getInstance()->getLang();
        if ($forceOff) {
            $player->setAllowFlight(false);
            $player->setFlying(false);
            $player->resetFallDistance();
            $player->sendMessage(C::colorize(str_replace(["{player}"], [$player->getName()], $config->getNested("fly.disabled"))));
        } else {
            if (!$player->getAllowFlight()) {
                $player->setAllowFlight(true);
                $player->sendMessage(C::colorize(str_replace(["{player}"], [$player->getName()], $config->getNested("fly.enabled"))));
            } else {
                $player->setAllowFlight(false);
                $player->setFlying(false);
                $player->resetFallDistance();
                $player->sendMessage(C::colorize(str_replace(["{player}"], [$player->getName()], $config->getNested("fly.disabled"))));
            }
        }
    }

    /**
     * Returns an online player whose name begins with or equals the given string (case insensitive).
     * The closest match will be returned, or null if there are no online matches.
     *
     * @param string $name The prefix or name to match.
     * @return Player|null The matched player or null if no match is found.
     */
    public static function getPlayerByPrefix(string $name): ?Player {
        $found = null;
        $name = strtolower($name);
        $delta = PHP_INT_MAX;

        /** @var Player[] $onlinePlayers */
        $onlinePlayers = Server::getInstance()->getOnlinePlayers();

        foreach ($onlinePlayers as $player) {
            if (stripos($player->getName(), $name) === 0) {
                $curDelta = strlen($player->getName()) - strlen($name);

                if ($curDelta < $delta) {
                    $found = $player;
                    $delta = $curDelta;
                }

                if ($curDelta === 0) {
                    break;
                }
            }
        }

        return $found;
    }

    /**
     * @param int $level
     * @return int
     */
    public static function getExpToLevelUp(int $level): int
    {
        if ($level <= 15) {
            return 2 * $level + 7;
        } else if ($level <= 30) {
            return 5 * $level - 38;
        } else {
            return 9 * $level - 158;
        }
    }

    public static function parseShorthandAmount($shorthand): float|int
    {
        $multipliers = [
            'k' => 1000,
            'm' => 1000000,
            'b' => 1000000000,
        ];
        $lastChar = strtolower(substr($shorthand, -1));
        if (isset($multipliers[$lastChar])) {
            $multiplier = $multipliers[$lastChar];
            $shorthand = substr($shorthand, 0, -1);
        } else {
            $multiplier = 1;
        }

        return intval($shorthand) * $multiplier;
    }

    public static function translateShorthand($amount): string
    {
        $multipliers = [
            1000000000 => 'b',
            1000000 => 'm',
            1000 => 'k',
        ];

        foreach ($multipliers as $multiplier => $shorthand) {
            if ($amount >= $multiplier) {
                $result = number_format($amount / $multiplier, 2) . $shorthand;
                return $result;
            }
        }

        return (string)$amount;
    }

    public static function translateTime(int $seconds): string
    {
        $timeUnits = [
            'w' => 60 * 60 * 24 * 7,
            'd' => 60 * 60 * 24,
            'h' => 60 * 60,
            'm' => 60,
            's' => 1,
        ];

        $parts = [];

        foreach ($timeUnits as $unit => $value) {
            if ($seconds >= $value) {
                $amount = floor($seconds / $value);
                $seconds %= $value;
                $parts[] = $amount . $unit;
            }
        }

        return implode(', ', $parts);
    }

    /**
     * @param int $integer
     * @return string
     */
    public static function getRomanNumeral(int $integer): string
    {
        $romanString = "";
        while ($integer > 0) {
            $romanNumeralConversionTable = [
                'M' => 1000,
                'CM' => 900,
                'D' => 500,
                'CD' => 400,
                'C' => 100,
                'XC' => 90,
                'L' => 50,
                'XL' => 40,
                'X' => 10,
                'IX' => 9,
                'V' => 5,
                'IV' => 4,
                'I' => 1
            ];
            foreach ($romanNumeralConversionTable as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $romanString .= $rom;
                    break;
                }
            }
        }
        return $romanString;
    }

    public static function secondsToTicks(int $seconds) : int {
        return $seconds * 20;
    }

    public static function checkRequestTimeout(string $targetName, string $type): void {
        if (isset(self::${$type}[$targetName])) {
            $request = self::${$type}[$targetName];
            if ((time() - $request['time']) >= 60) { // 60 seconds
                $requesterName = $request['requester'];
                $requester = self::getPlayerByPrefix($requesterName);
                $target = self::getPlayerByPrefix($targetName);
    
                if ($requester !== null && $requester->isOnline()) {
                    $requester->sendMessage(C::colorize("&r&l&c(!) &r&cYour teleport request to " . $targetName . " has expired."));
                }
    
                if ($target !== null && $target->isOnline()) {
                    foreach (Loader::getInstance()->getLang()->getAll()['tpa']['timed-out'] as $line) {
                        $target->sendMessage(C::colorize(str_replace(["{player_name}", "{time}", "{player}"], [$requesterName, "60", $requesterName], $line)));
                    }
                }
    
                self::$lastTpaRequester[$targetName] = $requesterName;
                unset(self::${$type}[$targetName]);
            }
        }
    }
    
    public static function handleExpiredTpaRequests(Player $player): void {
        if (isset(self::$lastTpaRequester[$player->getName()])) {
            $lastRequesterName = self::$lastTpaRequester[$player->getName()];
    
            $player->sendMessage(C::colorize("&cYou have no pending teleport requests."));
            unset(self::$lastTpaRequester[$player->getName()]);
        }
    }

    public static function createDefaultRulesFile(string $filePath): void
    {
        $defaultRules = [
            "[1] Be respectful",
            "[2] Be ethical",
            "[3] Use common sense",
        ];

        file_put_contents($filePath, implode(PHP_EOL, $defaultRules));
    }
}
