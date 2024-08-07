<?php

namespace ecstsy\essentialsx\Commands\SubCommands\Exp;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use pocketmine\world\sound\XpCollectSound;

class addExpSubCommand extends BaseSubCommand {

    public function prepare(): void
    {
        $this->setPermission($this->getPermission());
        $this->registerArgument(0, new RawStringArgument("player", false));
        $this->registerArgument(1, new IntegerArgument("amount", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Loader::getInstance()->getLang();
        $player = Utils::getPlayerByPrefix($args["player"]);
        $amount = (int) $args["amount"];

        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used by players.");
            return;
        }

        if (!($player = Utils::getPlayerByPrefix($args["player"]))) {
            $sender->sendMessage(C::colorize($config->getNested("xp.player_not_found", "&cPlayer not found.")));
            return;
        }

        if ($amount <= 0) {
            $sender->sendMessage(C::colorize($config->getNested("xp.invalid_amount", "&cAmount must be a positive integer.")));
            return;
        }

        $player->getXpManager()->addXp($amount);
        $sender->getWorld()->addSound($sender->getPosition(), new XpCollectSound());
        $sender->sendMessage(C::GREEN . str_replace(["{amount}", "{player}", "{new_xp}"], [number_format($amount), $player->getName(), number_format($player->getXpManager()->getCurrentTotalXp())], $config->getNested("xp.add_success", "&aAdded {amount} XP to {player}. Their new XP is {new_xp}.")));
    }

    public function getPermission(): string {
        return "essentialsx.add-xp";
    }
}
