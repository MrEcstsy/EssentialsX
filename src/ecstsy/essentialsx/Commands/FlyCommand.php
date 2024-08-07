<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;
use pocketmine\world\sound\XpLevelUpSound;

class FlyCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        Utils::toggleFlight($sender);
        $sender->getWorld()->addSound($sender->getPosition()->asVector3(), new XpLevelUpSound(10000));
    }

    public function getPermission(): string {
        return "essentialsx.fly";
    }
}