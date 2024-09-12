<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use pocketmine\world\sound\XpLevelUpSound;

class GMSCommand extends BaseCommand {

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

        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : $sender;
        $config = Loader::getInstance()->getLang();
        $gamemode = Utils::matchGameMode("survival");

        $player->setGamemode($gamemode);
        $sender->sendMessage(C::colorize(str_replace(["{player}", "{gamemode}"], [$player->getName(), $gamemode->getEnglishName()], $config->getNested("gamemode.set_gamemode", "&aSet {player}'s game mode to {gamemode}."))));
        $sender->getWorld()->addSound($sender->getPosition()->asVector3(), new XpLevelUpSound(100000));


    }

    public function getPermission(): string {
        return "essentialsx.gamemode";
    }
}