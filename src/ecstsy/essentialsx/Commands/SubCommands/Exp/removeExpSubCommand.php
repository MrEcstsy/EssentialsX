<?php

namespace ecstsy\essentialsx\Commands\SubCommands\Exp;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use pocketmine\world\sound\FizzSound;

class removeExpSubCommand extends BaseSubCommand
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
        $player = Utils::getPlayerByPrefix($args["player"]);
        $amount = (int) $args["amount"];

        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command must be used in-game.");
            return;
        }

        if (!$player) {
            $sender->sendMessage(C::RED . "Player not found.");
            return;
        }

        if ($amount <= 0) {
            $sender->sendMessage(C::RED . "Amount must be a positive integer.");
            return;
        }

        if ($amount > $player->getXpManager()->getCurrentTotalXp()) {
            $sender->sendMessage(C::RED . "{$player->getName()} does not have that much XP.");
            return;
        }

        $player->getXpManager()->subtractXp($amount);
        $newXp = $player->getXpManager()->getCurrentTotalXp();
        $sender->sendMessage(C::GREEN . str_replace(["{amount}", "{player}", "{new_xp}"], [number_format($amount), $player->getName(), number_format($newXp)], $config->getNested("xp.remove_success", "&aRemoved {amount} XP from {player}. Their new XP is {new_xp}.")));
        $sender->getWorld()->addSound($sender->getPosition(), new FizzSound());
    }

    public function getPermission(): string {
        return "essentialsx.remove-xp";
    }
}