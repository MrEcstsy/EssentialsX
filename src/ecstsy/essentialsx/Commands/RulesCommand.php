<?php

namespace ecstsy\EssentialsX\Commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

class RulesCommand extends BaseCommand {

    private const RULES_PER_PAGE = 9;
    private static array $cachedRules = [];
    private static string $rulesFilePath = "";

    public function prepare(): void {
        $this->setPermission("essentialsx.default");
        $this->registerArgument(0, new IntegerArgument("page", true));

        self::$rulesFilePath = Loader::getInstance()->getDataFolder() . "rules.txt";
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $page = isset($args["page"]) ? max(1, (int)$args["page"]) : 1;
        $config = Loader::getInstance()->getLang();

        if (!file_exists(self::$rulesFilePath)) {
            $sender->sendMessage(C::colorize("&r&cError: &4File rules.txt does not exist. Creating one for you."));
            Utils::createDefaultRulesFile(self::$rulesFilePath);
            return; 
        }

        if (empty(self::$cachedRules)) {
            self::$cachedRules = file(self::$rulesFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }

        $totalRules = count(self::$cachedRules);
        $totalPages = (int) ceil($totalRules / self::RULES_PER_PAGE);

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $startIndex = ($page - 1) * self::RULES_PER_PAGE;
        $rulesOnPage = array_slice(self::$cachedRules, $startIndex, self::RULES_PER_PAGE);

        $header = str_replace(
            ["{page}", "{max_page}"],
            [$page, $totalPages],
            C::colorize($config->getNested("rules.header", "&aRules:"))
        );
        $sender->sendMessage($header);

        foreach ($rulesOnPage as $rule) {
            $sender->sendMessage(C::colorize("&7" . $rule));
        }

        if ($totalRules > self::RULES_PER_PAGE) {
            $nextPage = $page + 1 > $totalPages ? $totalPages : $page + 1;

            $footer = str_replace(
                ["{page}", "{next_page}"],
                [$page, $nextPage],
                C::colorize($config->getNested("rules.footer", "&ePage {page}/{totalPages}"))
            );
            $sender->sendMessage($footer);
        }
    }

    public function getPermission(): string {
        return "essentialsx.default";
    }

    /**
     * Clears the cached rules if the rules file is modified externally.
     */
    public static function clearCache(): void {
        self::$cachedRules = [];
    }
}
