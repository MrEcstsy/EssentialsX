<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class NickCommand extends BaseCommand {
    
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
    
        $nickname = implode(" ", $args);
        $config = Loader::getInstance()->getConfig();
        
        $maxNickLength = $config->get("max-nick-length", 15);
        $nicknamePrefix = $config->get("nickname-prefix", '~');
        $nickBlacklist = $config->get("nick-blacklist", []);
        $ignoreColorsInMaxNickLength = $config->get("ignore-colors-in-max-nick-length", false);
    
        if (!$ignoreColorsInMaxNickLength) {
            $nickname = TextFormat::clean($nickname);
        }
    
        $nicknameLength = strlen($nickname);
        if ($nicknameLength > $maxNickLength) {
            $sender->sendMessage(C::RED . "Nickname cannot exceed $maxNickLength characters.");
            return;
        }
    
        foreach ($nickBlacklist as $blacklisted) {
            if (stripos($nickname, $blacklisted) !== false) {
                $sender->sendMessage(C::RED . "Nickname contains blacklisted phrase: $blacklisted");
                return;
            }
        }
    
        if ($sender->hasPermission("essentialsx.nick.hideprefix")) {
            $sender->setDisplayName($nickname);
        } else {
            $sender->setDisplayName($nicknamePrefix . $nickname);
        }
        $sender->sendMessage(C::GREEN . "Your nickname has been set to: " . $nicknamePrefix . $nickname);
    }

    public function getPermission(): string {
        return "essentialsx.nick";
    }
}