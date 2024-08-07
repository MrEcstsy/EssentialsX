<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class HomesCommand extends BaseCommand {

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

        $homes = Loader::getHomeManager()->getHomeList($sender->getUniqueId());

        if (empty($homes)) {
            $sender->sendMessage(C::RED . "You have no homes.");
            return;
        } else {
            $sender->sendMessage(C::GOLD . "Your homes:");
            foreach ($homes as $home) {
                $phome = Loader::getHomeManager()->getPlayerHome($sender->getUniqueId(), $home->getName());
                $sender->sendMessage(C::GREEN . "- " . $home->getName() . ": " . $phome->getPosition()->getFloorX() . ", " . $phome->getPosition()->getFloorY() . ", " . $phome->getPosition()->getFloorZ() . " (" . $phome->getWorld()->getFolderName() . ")");
            }
        }

        $homeName = isset($args["home"]) ? $args["home"] : null;

        if ($homeName !== null) {
            $home = Loader::getHomeManager()->getPlayerHome($sender->getUniqueId(), $homeName);
            if ($home !== null) {
                $home->teleport($sender);
                $sender->sendMessage(C::GREEN . "Teleported to home '{$homeName}'.");
            } else {
                $sender->sendMessage(C::RED . "Home '{$homeName}' does not exist.");
            }
        } 
    }

    public function getPermission(): string {
        return "essentialsx.default";
    }
}