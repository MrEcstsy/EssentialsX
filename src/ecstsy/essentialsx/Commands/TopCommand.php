<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use pocketmine\world\Position;

class TopCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize("&r&cError: &4Only in-game players can use &ctop&4."));
            return;
        }

        $position = $sender->getPosition();
        $world = $sender->getWorld();
        $highestY = $world->getHighestBlockAt($position->x, $position->z);
        $config = Loader::getInstance()->getLang();

        $sender->teleport(new Vector3($position->x, $highestY, $position->z));
        $sender->sendMessage(C::colorize($config->getNested("top.success")));
    }

    public function getPermission(): string {
        return "essentialsx.top";
    }
}
