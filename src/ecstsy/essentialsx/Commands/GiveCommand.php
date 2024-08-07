<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use pocketmine\world\sound\PopSound;

class GiveCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new RawStringArgument("item", false));
        $this->registerArgument(2, new IntegerArgument("amount", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $amount = isset($args["amount"]) ? $args["amount"] : 1;
        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : $sender;
        $item = isset($args["item"]) ? StringToItemParser::getInstance()->parse($args["item"]) : null;

        if (!$player instanceof Player) {
            $sender->sendMessage(C::RED . "Invalid player.");
            return;
        }

        if (!$item) {
            $sender->sendMessage(C::RED . "Invalid item.");
            return;
        }

        $item->setCount($amount);
        $player->getInventory()->addItem($item);

        $player->getWorld()->addSound($player->getLocation(), new PopSound());
        $sender->sendMessage(C::GREEN . "Successfully give {$amount} {$item->getName()} to {$player->getName()}.");
    }

    public function getPermission(): string {
        return "essentialsx.give";
    }
}