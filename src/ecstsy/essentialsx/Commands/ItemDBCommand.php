<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\item\Durable;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class ItemDBCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        $hand = $sender->getInventory()->getItemInHand();

        $sender->sendMessage(C::GOLD . "Item: " . C::RED . $hand->getVanillaName());
        if ($hand instanceof Durable) {
            $usesLeft = $hand->getMaxDurability() - $hand->getDamage();
            $sender->sendMessage(C::GOLD . "This tool has " . C::RED . $usesLeft . C::GOLD . " uses left.");
        }
    }

    public function getPermission(): string {
        return "essentialsx.itemdb";
    }
}