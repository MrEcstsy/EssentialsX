<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class BalanceCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", true));
    }


    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }
    
        $config = Loader::getInstance()->getLang();
    
        if (empty($args["name"])) {
            $session = Loader::getPlayerManager()->getSession($sender);
            $sender->sendMessage(C::colorize(str_replace("{balance}", number_format($session->getBalance()), $config->getNested("balance.display"))));
        } else {
            $player = Utils::getPlayerByPrefix($args["name"]);
    
            if ($player !== null) {
                $session = Loader::getPlayerManager()->getSession($player);
                $sender->sendMessage(C::colorize(str_replace(["{player}", "{balance}"], [$player->getName(), number_format($session->getBalance())], $config->getNested("balance.display-other"))));
            } else {
                $sender->sendMessage(C::RED . "Error: " . C::DARK_RED . "Player not found.");
                return;
            }
        }
    }
    

    public function getPermission(): string {
        return "essentialsx.default";
    }
}