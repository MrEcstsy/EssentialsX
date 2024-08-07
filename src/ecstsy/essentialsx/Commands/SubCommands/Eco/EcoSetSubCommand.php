<?php

namespace ecstsy\essentialsx\Commands\SubCommands\Eco;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

class EcoSetSubCommand extends BaseSubCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new IntegerArgument("amount", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : null;
        $amount = isset($args["amount"]) ? $args["amount"] : null;
        $config = Utils::getConfiguration(Loader::getInstance(), "config.yml");

        if ($player === null) {
            $sender->sendMessage(C::RED . "Error: " . C::DARK_RED . "Player not found.");
            return;
        } else {
            $session = Loader::getPlayerManager()->getSession($player);

            if ($amount === null) {
                $sender->sendMessage(C::RED . "Error: " . C::DARK_RED . "Invalid amount.");
            } else {
                $session->setBalance($amount);
                $sender->sendMessage(C::colorize("&r&aYou set " . $player->getNameTag() . "&a's balance to " . $config->get("currency-symbol") . number_format($amount) . "."));
                $player->sendMessage(C::colorize("&r&aYour balance was set to " . $config->get("currency-symbol") . number_format($amount) . "."));
            }
        }
    }

    public function getPermission(): string {
        return "essentialsx.eco-give";
    }
}