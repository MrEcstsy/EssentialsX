<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class RemoveWarpCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("warp", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        $warpName = isset($args["warp"]) ? $args["warp"] : null;

        if ($warpName !== null) {
            $warp = Loader::getWarpManager()->getWarp($warpName);
            if ($warp !== null) { // Probably redundant 
                Loader::getWarpManager()->deleteWarp($warp);
                $sender->sendMessage(C::DARK_RED . "Warp '{$warpName}' has been deleted.");
            } else {
                $sender->sendMessage(C::RED . "Warp '{$warpName}' does not exist.");
            }
        } else {
            $sender->sendMessage(C::RED . "Warp '{$warpName}' does not exist.");
        }
    }

    public function getPermission(): string {
        return "essentialsx.remove-warp";
    }
}