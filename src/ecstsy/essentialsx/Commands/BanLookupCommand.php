<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class BanLookupCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("search", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (isset($args["search"])) {
            if ($args["search"] === "list") {
                $players = Server::getInstance()->getNameBans()->getEntries();
                $sender->sendMessage(TextFormat::GOLD . "Banned Players:");
                
                foreach ($players as $player) {
                    $sender->sendMessage(TextFormat::GOLD . "- " . TextFormat::RED . $player->getName());
                }
            } else {
                $info = Server::getInstance()->getNameBans()->getEntry($args["search"]);

                if ($info !== null) {
                    $sender->sendMessage(TextFormat::GOLD . "Ban Information for " . TextFormat::RED . $args["search"] . TextFormat::GOLD . ":");
                    $sender->sendMessage(TextFormat::GOLD . "Reason: " . TextFormat::RED . $info->getReason()); 
                } else {
                    $sender->sendMessage(TextFormat::GOLD . "Player " . TextFormat::RED . $args["search"] . TextFormat::GOLD . " is not banned.");
                }
            }
        }
    }

    public function getPermission(): string {
        return "essentialsx.ban-lookup";
    }
}