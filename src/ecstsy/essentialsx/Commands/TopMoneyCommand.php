<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;

class TopMoneyCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new IntegerArgument("page", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        $page = (int)($args["page"] ?? 1);
        $config = Loader::getInstance()->getLang();
        $topLimit = (int)$config->getNested("balance.balance-top-limit", 10);

        if ($topLimit <= 0) {
            $sender->sendMessage(C::RED . "The balance-top-limit must be greater than zero.");
            return;
        }

        $allPlayers = [];
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $session = Loader::getPlayerManager()->getSession($player);
            $balance = $session->getBalance();
            $allPlayers[$player->getName()] = (int)$balance;
        }

        arsort($allPlayers);

        $totalPlayers = count($allPlayers);
        if ($totalPlayers === 0) {
            $sender->sendMessage(C::RED . "There are no players with a balance to display.");
            return;
        }

        $maxPage = (int)ceil($totalPlayers / $topLimit);
        if ($page < 1) {
            $page = 1;
        } elseif ($page > $maxPage) {
            $page = $maxPage;
        }

        $start = ($page - 1) * $topLimit;
        $playersOnPage = array_slice($allPlayers, $start, $topLimit, true);

        $output = [];

        $date = date("m/d/y");

        $output[] = C::colorize(str_replace("{date}", $date, $config->getNested("balance.balance-top")));

        $output[] = C::colorize(str_replace(["{page}", "{max_page}"], [$page, $maxPage], $config->getNested("balance.balance-top-header")));

        $totalBalance = array_sum($allPlayers);
        $output[] = C::colorize(str_replace("{total}", number_format($totalBalance, 2), $config->getNested("balance.balance-top-total")));
        
        $place = $start + 1;
        foreach ($playersOnPage as $playerName => $balance) {
            $formattedLine = str_replace(
                ["{place}", "{player}", "{balance}"],
                [$place, $playerName, number_format($balance, 2)],
                $config->getNested("balance.balance-top-body")
            );
            $output[] = C::colorize($formattedLine);
            $place++;
        }

        foreach ($output as $line) {
            $sender->sendMessage($line);
        }
    }    

    public function getPermission(): string
    {
        return "essentialsx.default";
    }
}
