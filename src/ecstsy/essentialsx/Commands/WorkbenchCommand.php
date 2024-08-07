<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Events\Workbench\OpenWorkbenchPortableCraftingEvent;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class WorkbenchCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        ($ev = new OpenWorkbenchPortableCraftingEvent($sender))->call();
        if (!$ev->isCancelled()) {
            Utils::workBench()->send($sender);
        }
    }

    public function getPermission(): string {
        return "essentialsx.workbench";
    }
}