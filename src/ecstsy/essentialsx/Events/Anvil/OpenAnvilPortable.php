<?php

declare(strict_types=1);

namespace ecstsy\essentialsx\Events\Anvil;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

final class OpenAnvilPortable extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	public function __construct(Player $player){
		$this->player = $player;
	}
}