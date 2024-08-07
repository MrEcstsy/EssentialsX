<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class IPBanCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new TextArgument("reason", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Loader::getInstance()->getLang();
        $user = Utils::getPlayerByPrefix($args["name"]);
        $ip = $user->getServer()->getIp();
        $list = Server::getInstance()->getIpBans();
        $reason = isset($args["reason"]) ? implode(" ", array_slice($args, 1)) : $config->get("ip-ban.default-reason");

        if ($list->isBanned($ip)) { 
            $sender->sendMessage(TextFormat::RED . "This IP is already banned.");
        } else {
            $list->addBan($ip, $reason, null, $sender->getName());
            
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if ($player->getServer()->getIp() === $ip) {
                    $player->kick(str_replace(["{reason}"], [$reason], $config->get("ip-ban.message")));
                    $sender->sendMessage(TextFormat::GREEN . "Banned IP: " . $ip);
                }
            }
        }
    }

    public function getPermission(): string {
        return "essentialsx.ip-ban";
    }
}