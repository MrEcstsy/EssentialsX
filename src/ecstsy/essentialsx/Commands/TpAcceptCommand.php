<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class TpAcceptCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage(C::colorize("&cThis command can only be used in-game."));
            return;
        }

        if (!isset(Utils::$tpaRequests[$sender->getName()]) && !isset(Utils::$tpahereRequests[$sender->getName()])) {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cYou have no pending teleport requests."));
            return;
        }

        if (isset(Utils::$tpaRequests[$sender->getName()])) {
            $requesterName = Utils::$tpaRequests[$sender->getName()]['requester'];
            $requester = isset($requesterName) ? Utils::getPlayerByPrefix($requesterName) : null;

            if ($requester === null || !$requester->isOnline()) {
                $sender->sendMessage(C::colorize("&r&l&cError: &r&cThat player is not online."));
                unset(Utils::$tpaRequests[$sender->getName()]);
                return;
            }

            $requester->teleport($sender->getPosition());
            $sender->sendMessage(C::colorize("&r&l&a(!) &r&aYou have teleported " . $requester->getName() . " to your location."));
            $requester->sendMessage(C::colorize("&r&l&a(!) &r&aYou have been teleported to " . $sender->getName() . "."));
            unset(Utils::$tpaRequests[$sender->getName()]);
        } elseif (isset(Utils::$tpahereRequests[$sender->getName()])) {
            $requesterName = Utils::$tpahereRequests[$sender->getName()]['requester'];
            $requester = isset($requesterName) ? Utils::getPlayerByPrefix($requesterName) : null;

            if ($requester === null || !$requester->isOnline()) {
                $sender->sendMessage(C::colorize("&r&l&c(!) &r&cThat player is not online."));
                unset(Utils::$tpahereRequests[$sender->getName()]);
                return;
            }

            $sender->teleport($requester->getPosition());
            $sender->sendMessage(C::colorize("&r&l&a(!) &r&aYou have been teleported to " . $requester->getName() . "."));
            $requester->sendMessage(C::colorize("&r&l&a(!) &r&a" . $sender->getName() . " has accepted your teleport request."));
            unset(Utils::$tpahereRequests[$sender->getName()]);
        }

        return;
    }

    public function getPermission(): string
    {
        return "essentialsx.default";
    }
}
