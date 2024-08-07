<?php

namespace ecstsy\essentialsx\Commands\SubCommands\Spawn;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use pocketmine\world\particle\EndermanTeleportParticle;

class SetSpawnSubCommand extends BaseSubCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        $sender->getWorld()->setSpawnLocation($sender->getPosition());
        $sender->sendMessage(C::colorize("&aSpawn location set."));
        $sender->getWorld()->addParticle($sender->getPosition(), new EndermanTeleportParticle());
    }

    public function getPermission(): string {
        return "essentialsx.spawn-set";
    }
}