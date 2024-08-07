<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class NearCommand extends BaseCommand {
    
    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        $config = Utils::getConfiguration(Loader::getInstance(), "config.yml");
        $radius = $config->get("near-radius", 200);
        
        $nearby = [];

        foreach ($sender->getWorld()->getPlayers() as $p) {
            if ($p !== $sender) {
                $x = $p->getPosition()->getFloorX() - $sender->getPosition()->getFloorX();
                $y = $p->getPosition()->getFloorY() - $sender->getPosition()->getFloorY();
                $z = $p->getPosition()->getFloorZ() - $sender->getPosition()->getFloorZ();
                $dist = round(sqrt($x * $x + $y * $y + $z * $z));

                if ($dist <= $radius) {
                    $nearby[] = $p->getNameTag() . " ($dist blocks)";
                }
            }
        }

        if (count($nearby) > 0) {
            $sender->sendMessage(C::GOLD . "Players Within: " . C::RED . $radius . C::GOLD . " blocks: " . C::RED . implode(", ", $nearby));
        } else {
            $sender->sendMessage(C::GOLD . "No players found within " . C::RED . $radius . C::GOLD . " blocks.");
        }
    }

    public function getPermission(): string {
        return "essentialsx.near";
    }
}