<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Events\Anvil\OpenAnvilPortable;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class AnvilCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        ($ev = new OpenAnvilPortable($sender))->call();
        if (!$ev->isCancelled()) {
            Utils::anvil()->send($sender);
        }
    }

    public function getPermission(): string {
        return "essentialsx.anvil";
    }
}