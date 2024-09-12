<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Commands\SubCommands\Repair\RepairAllSubCommand;
use ecstsy\essentialsx\Loader;
use pocketmine\command\CommandSender;
use pocketmine\item\Durable;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class RepairCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerSubCommand(new RepairAllSubCommand(Loader::getInstance(), "all", "Repair all items in your inventory."));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize("&r&cError: &4Only in-game players can use &cfix&c."));
            return;
        }

        $item = $sender->getInventory()->getItemInHand();
        $config = Loader::getInstance()->getLang();

        if ($item->equals(VanillaItems::AIR()) || !$item instanceof Durable) {
            $sender->sendMessage(C::colorize($config->getNested("repair.invalid")));
            return;
        }

        if ($item instanceof Durable && $item->getDamage() > 0) {
            $item->setDamage(0);
            $sender->getInventory()->setItemInHand($item);
            $sender->sendMessage(C::colorize(str_replace("{item}", $item->getVanillaName(), $config->getNested("repair.success"))));
        } else {
            $sender->sendMessage(C::colorize($config->getNested("repair.invalid")));
        }
    }

    public function getPermission(): string {
        return "essentialsx.repair";
    }
}
