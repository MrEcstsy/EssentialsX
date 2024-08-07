<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class CreateWarpCommand extends BaseCommand {

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

        if ($warpName === null) {
            $sender->sendMessage(C::RED . "Usage: /setwarp <warpname>");
            return;
        }
    
        $warpManager = Loader::getWarpManager();
    
        if ($warpManager->getWarp($warpName) === null) {
            $warpManager->createWarp($sender, $warpName);
            $sender->sendMessage(C::GREEN . "Warp '{$warpName}' has been created.");
        } else {
            $sender->sendMessage(C::RED . "Warp '{$warpName}' already exists.");
        }
    }

    public function getPermission(): string {
        return "essentialsx.create-warp";
    }
}