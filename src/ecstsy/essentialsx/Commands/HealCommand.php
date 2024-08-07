<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class HealCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Utils::getConfiguration(Loader::getInstance(), "config.yml");
        
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        $session = Loader::getPlayerManager()->getSession($sender);

        if ($session->getCooldown("heal_command") === null || $session->getCooldown("heal_command") === 0) {
            $sender->setHealth($sender->getMaxHealth());
            $sender->sendMessage(C::GREEN . "Your health has been restored.");
            $session->addCooldown("heal_command", $config->getNested("command-cooldowns.heal"));
        } else {
            $sender->sendMessage(C::colorize("&cYou must wait &6" . Utils::translateTime($session->getCooldown("heal_command")) . " &cseconds before you can use this command again."));
        }
    }

    public function getPermission(): string {
        return "essentialsx.heal";
    }
}