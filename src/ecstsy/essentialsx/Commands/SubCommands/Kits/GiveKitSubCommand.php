<?php

namespace ecstsy\essentialsx\Commands\SubCommands\Kits;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class GiveKitSubCommand extends BaseSubCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new RawStringArgument("kit", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Utils::getConfiguration(Loader::getInstance(), "kits.yml");
        $kits = $config->getNested("kits", []);
        $kit = isset($args["kit"]) ? $args["kit"] : null;

        if (!isset($kits[$args["kit"]])) {
            $sender->sendMessage(C::RED . "Invalid kit name. Use /kit list to see available kits.");
            return;
        }

        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : $sender;

        if ($player instanceof Player) {
            $items = Utils::setupItems($config->getNested("kits.$kit.items"));
            
            foreach ($items as $item) {
                $player->getInventory()->addItem($item);
            }

            $sender->sendMessage(C::GOLD . "Kit '" . C::RED . $kit . C::GOLD . "' has been given to " . C::RED . $player->getName());

        }
    }

    public function getPermission(): string {
        return "essentialsx.give-kit";
    }
}