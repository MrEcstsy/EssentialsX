<?php

namespace ecstsy\essentialsx\Commands\SubCommands\Exp;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\utils\TextFormat as C;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class showExpSubCommand extends BaseSubCommand
{

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void
    {
        $this->setPermission($this->getPermission());
        $this->registerArgument(0, new RawStringArgument("player", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Loader::getInstance()->getLang();
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize($config->getNested("xp.in_game_only", "&cThis command must be used in-game.")));
            return;
        }

        if (!$sender->hasPermission('essentialsx.xp.see')) {
            $sender->sendMessage(C::colorize($config->getNested("xp.no_permission", "&cYou do not have permission to use this command.")));
            return;
        }

        $player = Utils::getPlayerByPrefix($args["player"]);
        if ($player === null) {
            $sender->sendMessage(C::colorize($config->getNested("xp.player_not_found", "&cPlayer not found.")));
            return;
        }

        $sender->sendMessage(C::colorize(str_replace(["{player}", "{xp}", "{level}", "{levelup}"], [$player->getNameTag(), number_format($player->getXpManager()->getCurrentTotalXp()), $player->getXpManager()->getXpLevel(), Utils::getExpToLevelUp($player->getXpManager()->getCurrentTotalXp())], $config->getNested("xp.show_info", "{player} §r§6has §r§c{xp} EXP §r§6(level §r§c{level}§r§6) §r§6and needs {levelup} more exp to level up."))));
    }

    public function getPermission(): string {
        return "essentialsx.show-xp";
    }
}
