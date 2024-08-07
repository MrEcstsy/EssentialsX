<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class PayCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new IntegerArgument("amount", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }
    
        $config = Utils::getConfiguration(Loader::getInstance(), "messages-eng.yml");
    
        if (count($args) < 2) {
            $sender->sendMessage(C::colorize("&r&f/pay &e<player> <amount> &6- Pays the specified player the given"));
            $sender->sendMessage(C::colorize("&r&6 amount of money"));
            return;
        }
    
        $targetName = array_shift($args);
        $amount = (int) array_shift($args);
    
        $senderSession = Loader::getInstance()->getPlayerManager()->getSession($sender);
    
        if (!is_numeric($amount) || $amount <= 0) {
            $sender->sendMessage(C::RED . "Error: " . C::DARK_RED . "Enter a valid amount.");
            return;
        }
    
        $targetPlayer = Utils::getPlayerByPrefix($targetName);
    
        if ($targetPlayer === null) {
            $sender->sendMessage(C::RED . "Error: " . C::DARK_RED . "Player not found.");
            return;
        }
    
        $targetSession = Loader::getInstance()->getPlayerManager()->getSession($targetPlayer);
    
        if ($senderSession->getBalance() < $amount) {
            $sender->sendMessage(C::RED . "You don't have sufficient funds.");
            return;
        }
    
        $maxBalance = $config->get("max-money");
        $remainingBalance = $maxBalance - $targetSession->getBalance();
        $transferAmount = min($amount, $remainingBalance);
    
        $senderSession->subtractBalance($transferAmount);
        $targetSession->addBalance($transferAmount);
    
        $sender->sendMessage(C::colorize("&r&a" . $config->get("currency-symbol") . number_format($transferAmount) . " has been sent to " . $targetPlayer->getNameTag() . "."));
        $targetPlayer->sendMessage(C::colorize("&r&a" . $config->get("currency-symbol") . number_format($transferAmount) . " &6has been received from " . $sender->getNameTag() . "."));
    }
    

    public function getPermission(): string {
        return "essentialsx.default";
    }
}