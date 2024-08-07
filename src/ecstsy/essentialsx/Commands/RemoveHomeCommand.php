<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class RemoveHomeCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("home", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        $homeName = isset($args["home"]) ? $args["home"] : null;

        if ($homeName !== null) {
            $home = Loader::getHomeManager()->getPlayerHome($sender->getUniqueId(), $homeName);
            if ($home !== null) { // Probably redundant 
                Loader::getHomeManager()->deleteHome($home);
                $sender->sendMessage(C::RED . "Successfully removed home '{$homeName}'!");
            } else {
                $sender->sendMessage(C::RED . "Home '{$homeName}' does not exist.");
            }
        } else {
            $sender->sendMessage(C::RED . "Home '{$homeName}' does not exist.");
        }
    }

    public function getPermission(): string {
        return "essentialsx.default";
    }
}