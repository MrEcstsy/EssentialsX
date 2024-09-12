<?php

namespace ecstsy\essentialsx\Commands\SubCommands\Repair;

use CortexPE\Commando\BaseSubCommand;
use ecstsy\essentialsx\Loader;
use pocketmine\command\CommandSender;
use pocketmine\item\Durable;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class RepairAllSubCommand extends BaseSubCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize("&r&cError: &4Only in-game players can use &cfix&c."));
            return;
        }

        $inventory = $sender->getInventory();
        $armorInventory = $sender->getArmorInventory();
        $config = Loader::getInstance()->getLang();
        $repairedItems = [];

        foreach ($inventory->getContents() as $slot => $item) {
            if ($item instanceof Durable && $item->getDamage() > 0) {
                $item->setDamage(0);
                $inventory->setItem($slot, $item);
                $repairedItems[] = $item->getVanillaName();
            }
        }

        foreach ($armorInventory->getContents() as $slot => $item) {
            if ($item instanceof Durable && $item->getDamage() > 0) {
                $item->setDamage(0);
                $armorInventory->setItem($slot, $item);
                $repairedItems[] = $item->getVanillaName();
            }
        }

        if (empty($repairedItems)) {
            $sender->sendMessage(C::colorize($config->getNested("repair.all-none")));
        } else {
            $repairedItemsMessage = implode(", ", $repairedItems);
            $message = str_replace("{item}", $repairedItemsMessage, $config->getNested("repair.success"));
            $sender->sendMessage(C::colorize($message));
        }
    }

    public function getPermission(): string {
        return "essentialsx.repairall";
    }
}
