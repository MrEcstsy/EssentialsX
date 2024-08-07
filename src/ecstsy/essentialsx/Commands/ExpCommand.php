<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Commands\SubCommands\Exp\addExpSubCommand;
use ecstsy\essentialsx\Commands\SubCommands\Exp\removeExpSubCommand;
use ecstsy\essentialsx\Commands\SubCommands\Exp\setExpSubCommand;
use ecstsy\essentialsx\Commands\SubCommands\Exp\showExpSubCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;

class ExpCommand extends BaseCommand {

    public function prepare(): void
    {
        $this->setPermission($this->getPermission());

        $this->registerSubCommand(new AddExpSubCommand(Loader::getInstance(), "add", "add exp to a player", ["give", "insert"]));
        $this->registerSubCommand(new removeExpSubCommand(Loader::getInstance(), "remove", "remove exp from a player", ["take", "deduct", "subtract"]));
        $this->registerSubCommand(new setExpSubCommand(Loader::getInstance(), "set", "set the exp of a player"));
        $this->registerSubCommand(new showExpSubCommand(Loader::getInstance(), "show", "show the exp of a player", ["get", "view"]));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Loader::getInstance()->getLang();

        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize($config->getNested("xp.in_game_only", "&cThis command must be used in-game.")));
            return;
        }

        $levelup = Utils::getExpToLevelUp($sender->getXpManager()->getCurrentTotalXp());
        $sender->sendMessage(C::colorize(str_replace(["{player}", "{exp}", "{level}", "{levelup}"], [$sender->getNameTag(), number_format($sender->getXpManager()->getCurrentTotalXp()), $sender->getXpManager()->getXpLevel(), number_format($levelup)], $config->getNested("xp.self_info", "{player} §r§6has §r§c{exp} EXP §r§6(level §r§c{level}§r§6) §r§6and needs {levelup} more exp to level up."))));
    }

    public function getPermission(): string {
        return "essentialsx.default";
    }
}