<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as C;

class TpaCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $config = Loader::getInstance()->getLang()->getAll();
        $tpaCfg = $config['tpa'];
        if (!($sender instanceof Player)) {
            $sender->sendMessage(C::colorize("&cThis command can only be used in-game."));
            return;
        }

        $target = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : null;

        if ($target === null || !$target->isOnline()) {
            $sender->sendMessage(C::colorize("&r&cError: &4Player not found."));
            return;
        }

        Utils::$tpaRequests[$target->getName()] = [
            'requester' => $sender->getName(),
            'time' => Utils::secondsToTicks($tpaCfg['expire-time'])
        ];

        Utils::$lastTpaRequester[$target->getName()] = $sender->getName();

        foreach ($tpaCfg['sent'] as $line) {
            $sender->sendMessage(C::colorize(str_replace(["{target_name}", "{target}"], [$target->getNameTag(), $target->getName()], $line)));
        }

        foreach ($tpaCfg['received'] as $line) {
            $line = str_replace(
                ["{player_name}", "{time}", "{player}"],
                [$sender->getNameTag(), $tpaCfg['expire-time'], $sender->getName()],
                $line
            );
            $target->sendMessage(C::colorize($line));
        }

        Loader::getInstance()->getScheduler()->scheduleDelayedTask(new class($target->getName()) extends Task {
            private string $targetName;

            public function __construct(string $targetName) {
                $this->targetName = $targetName;
            }

            public function onRun(): void {
                Utils::checkRequestTimeout($this->targetName, "tpaRequests");
            }
        }, Utils::secondsToTicks($tpaCfg['expire-time'])); 
    }

    public function getPermission(): string {
        return "essentialsx.default";
    }
}