<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Commands\SubCommands\Kits\GiveKitSubCommand;
use ecstsy\essentialsx\Commands\SubCommands\Kits\ListSubCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class KitCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("kit", true));

        $this->registerSubCommand(new ListSubCommand(Loader::getInstance(), "list", "list all kits"));
        $this->registerSubCommand(new GiveKitSubCommand(Loader::getInstance(), "give", "give a kit to a player"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "&cThis command must be used in-game.");
            return;
        }
    
        $session = Loader::getPlayerManager()->getSession($sender);
        $config = Utils::getConfiguration(Loader::getInstance(), "config.yml");
        $kitConfig = Utils::getConfiguration(Loader::getInstance(), "kits.yml");
        
        $kit = isset($args["kit"]) ? $args["kit"] : null;
    
        if ($kit !== null) {
            if (!$sender->hasPermission("essentialsx.kit.$kit")) {
                $sender->sendMessage(C::RED . "You do not have permission to claim this kit.");
                return;
            }
    
            if (!isset($kitConfig->getNested("kits", [])[$kit])) {
                $sender->sendMessage(C::RED . "&cThe kit '$kit' does not exist.");
                return;
            }
    
            $kitData = $kitConfig->get("kits.$kit");
    
            if ($session->getCooldown($kit) === null || $session->getCooldown($kit) === 0) {
                $items = Utils::setupItems($kitConfig->getNested("kits.$kit.items"));
                foreach ($items as $item) {
                    $sender->getInventory()->addItem($item);
                }
                
                $session->addCooldown($kit, $kitConfig->getNested("kits.$kit.cooldown"));
                $sender->sendMessage(C::GOLD . "Kit '" . C::RED . $kit . C::GOLD . "' has been given.");
                
            } else {
                $sender->sendMessage(C::RED . "This kit is on cooldown for " . Utils::translateTime($session->getCooldown($kit)) . ".");
            }
        } else {
            $sender->sendMessage(C::RED . "Available commands: /kit list, /kit give <player> <kit>");
        }
    }
    
    public function getPermission(): string {
        return "essentialsx.default";
    }
}