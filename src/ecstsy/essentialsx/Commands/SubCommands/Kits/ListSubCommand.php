<?php

namespace ecstsy\essentialsx\Commands\SubCommands\Kits;

use CortexPE\Commando\BaseSubCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

class ListSubCommand extends BaseSubCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Utils::getConfiguration(Loader::getInstance(), "kits.yml");
        $kits = $config->getNested("kits", []);

        $sender->sendMessage(C::colorize("&r&6Available kits: &c" . implode(", ", array_keys($kits))));
    }

    public function getPermission(): string {
        return "essentialsx.default";
    }
}