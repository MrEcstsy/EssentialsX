<?php

namespace ecstsy\EssentialsX\Utils; 

use pocketmine\item\{Armor, Item, Tool, VanillaItems};

trait EnchantmentTrait
{


    abstract public function getId(): string;

    abstract public function getMcpeId(): int;

    /**
     * @return bool
     */
    public function isTreasure(): bool
    {
        return false;
    }

    /**
     * @return int[]
     */
    public function getIncompatibles(): array
    {
        return [];
    }

    /**
     * @param Item $item
     * @return bool
     * default it returns global compatibilities
     */
    public function isItemCompatible(Item $item): bool
    {
        return $item instanceof Armor ||
            $item instanceof Tool ||
            in_array($item->getVanillaName(), [
                VanillaItems::FISHING_ROD()->getVanillaName(),
                VanillaItems::BOW()->getVanillaName(),
                VanillaItems::SHEARS()->getVanillaName(),
                VanillaItems::FLINT_AND_STEEL()->getVanillaName()
            ]);
    }
}