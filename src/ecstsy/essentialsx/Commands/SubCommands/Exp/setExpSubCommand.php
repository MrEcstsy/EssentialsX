<?php

namespace ecstsy\essentialsx\Commands\SubCommands\Exp;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;

class setExpSubCommand extends BaseSubCommand
{

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void
    {
        $this->setPermission($this->getPermission());
        $this->registerArgument(0, new RawStringArgument("player", false));
        $this->registerArgument(1, new IntegerArgument("amount", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Loader::getInstance()->getLang();
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize($config->getNested("xp.in_game_only", "&cThis command must be used in-game.")));
            return;
        }

        $player = Utils::getPlayerByPrefix($args["player"]);
        if (!$player) {
            $sender->sendMessage(C::colorize($config->getNested("xp.player_not_found", "&cPlayer not found.")));
            return;
        }
        
        $amount = (int) $args["amount"];
        if ($amount < 0) {
            $sender->sendMessage(C::colorize($config->getNested("xp.invalid_amount", "&cAmount must be a non-negative integer.")));
            return;
        }

        $player->getXpManager()->setCurrentTotalXp($amount);
        $sender->sendMessage(C::colorize(str_replace(["{player}", "{amount}"], [$player->getName(), number_format($amount)], $config->getNested("xp.set_success", "&aSet {player}'s XP to {amount}."))));
    }

    public function getPermission(): string {
        return "essentialsx.set-xp";
    }
}
