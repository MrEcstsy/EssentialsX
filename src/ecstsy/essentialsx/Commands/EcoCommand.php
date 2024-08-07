<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Commands\SubCommands\Eco\EcoGiveSubCommand;
use ecstsy\essentialsx\Commands\SubCommands\Eco\EcoResetSubCommand;
use ecstsy\essentialsx\Commands\SubCommands\Eco\EcoSetSubCommand;
use ecstsy\essentialsx\Commands\SubCommands\Eco\EcoTakeSubCommand;
use ecstsy\essentialsx\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class EcoCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerSubCommand(new EcoGiveSubCommand(Loader::getInstance(), "give", "give money to a player"));
        $this->registerSubCommand(new EcoTakeSubCommand(Loader::getInstance(), "take", "take money from a player"));
        $this->registerSubCommand(new EcoSetSubCommand(Loader::getInstance(), "set", "set the balance of a player"));
        $this->registerSubCommand(new EcoResetSubCommand(Loader::getInstance(), "reset", "reset the balance of a player"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        $messages = [
            "&r&6Description: &f" . $this->getDescription(),
            "&r&6Usage(s):",
            "&r&f/eco give &e<player> <amount> &6- Gives the specified player the",
            "&r&fspecified amount of money",
            "&r&f/eco take &e<player> <amount> &6- Takes the specified amount of",
            "&r&fmoney from the specified player",
            "&r&f/eco set &e<player> <amount> &6- Sets the specified player's",
            "&r&fbalance to the specified amount of money",
            "&r&f/eco reset &e<player> [amount] &6- Resets the specified player's",
            "&r&fbalance to the server's starting balance"
        ];
        
        foreach ($messages as $message) {
            $sender->sendMessage(C::colorize($message));
        }
    }

    public function getPermission(): string {
        return "essentialsx.eco";
    }
}