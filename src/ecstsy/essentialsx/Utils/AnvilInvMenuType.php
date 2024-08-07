<?php

declare(strict_types=1);

namespace ecstsy\essentialsx\Utils;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\graphic\InvMenuGraphic;
use muqsit\invmenu\type\InvMenuType;
use muqsit\invmenu\type\util\InvMenuTypeBuilders;
use pocketmine\block\inventory\AnvilInventory;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use pocketmine\world\Position;

final class AnvilInvMenuType implements InvMenuType {

    private InvMenuType $inner;

    public function __construct() {
        $this->inner = InvMenuTypeBuilders::BLOCK_FIXED()->setBlock(VanillaBlocks::ANVIL())->setSize(3)->setNetworkWindowType(WindowTypes::ANVIL)->build();
    }

    public function createGraphic(InvMenu $menu, Player $player): ?InvMenuGraphic
    {
        return $this->inner->createGraphic($menu, $player);
    }

    public function createInventory() : Inventory {
        return new AnvilInventory(Position::fromObject(Vector3::zero(), null));
    }
}