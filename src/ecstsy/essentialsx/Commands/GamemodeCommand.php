<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;

class GamemodeCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
        
        $this->registerArgument(0, new RawStringArgument("gamemode", false));
        $this->registerArgument(1, new RawStringArgument("name", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $config = Loader::getInstance()->getLang();
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        if (isset($args["gamemode"])) {
            $gamemode = Utils::matchGameMode($args["gamemode"]);

            if ($gamemode === null) {
                $sender->sendMessage(C::colorize($config->getNested("gamemode.invalid_gamemode", "&cInvalid gamemode.")));
                return;
            }

            $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : $sender;

            if ($player === null) {
                $sender->sendMessage(C::colorize(str_replace(["{player}"], [$args["name"]], $config->getNested("gamemode.player_not_found", "&cPlayer not found."))));
                return;
            }

            if (!$sender->hasPermission("essentialsx.gamemode")) { 
                $sender->sendMessage(C::colorize($config->getNested("gamemode.no_permission", "&cYou do not have permission to use this command.")));
                return;
            }

            $player->setGamemode($gamemode);
            $sender->sendMessage(C::colorize(str_replace(["{player}", "{gamemode}"], [$player->getName(), $gamemode->getEnglishName()], $config->getNested("gamemode.set_gamemode", "&aSet {player}'s game mode to {gamemode}."))));
        }
    }

    public function getPermission(): string {
        return "essentialsx.gamemode";
    }
}
