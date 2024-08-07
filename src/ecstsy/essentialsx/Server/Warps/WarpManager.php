<?php

declare(strict_types=1);

namespace ecstsy\essentialsx\Server\Warps;

use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Queries;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class WarpManager
{

    use SingletonTrait;

    /** @var Warp[] */
    private array $warps = [];

    public function __construct(
        public Loader $plugin
    ){
        self::setInstance($this);

        $this->loadWarps();
    }

    /**
     * Store all warp data in $warps property
     *
     * @return void
     */
    private function loadWarps() : void
    {
        Loader::getDatabase()->executeSelect(Queries::WARPS_SELECT, [], function (array $rows) : void {
            foreach ($rows as $row) {
                $this->warps[] = new Warp(
                    $row["warp_name"],
                    $row["world_name"],
                    $row["x"],
                    $row["y"],
                    $row["z"]
                );
            }
        });
    }

    /**
     * Create a warp
     *
     * Example: WarpManager::createWarp("warp1")
     *
     * @param Player $player
     * @param string $war[_name
     */
    public function createWarp(Player $player, string $warp_name) : void
    {
        $pos  = $player->getPosition();
        $args = [
            "warp_name"  => $warp_name,
            "world_name" => $player->getWorld()->getFolderName(),
            "x"          => $pos->getFloorX(),
            "y"          => $pos->getFloorY(),
            "z"          => $pos->getFloorZ()
        ];

        Loader::getDatabase()->executeInsert(Queries::WARPS_CREATE, $args);

        $this->warps[] = new Warp(
            $args["warp_name"],
            $args["world_name"],
            $args["x"],
            $args["y"],
            $args["z"]
        );
    }

    /**
     * Get Warp by Name
     *
     * @param string $warp_name
     * @return Warp|null
     */
    public function getWarp(string $warp_name) : ?Warp
    {
        foreach ($this->warps as $warp) {
            # If the name does not match, skip to the next one
            if ($warp->getName() !== $warp_name) {
                continue;
            }
            return $warp;
        }
        return null;

    }

    /**
     * Get Warp List
     *
     * @return array|null
     */
    public function getWarpList() : ?array
    {
        $fetched = [];
        foreach ($this->warps as $warp) {
            $fetched[] = $warp;
        }

        # If the fetched array is empty, return null. Else, return fetched.
        return empty($fetched) ? null : $fetched;
    }

    /**
     * Delete a warp
     *
     * @param Warp   $warp
     * @return void
     */
    public function deleteWarp(Warp $warp) : void
    {
        Loader::getDatabase()->executeChange(Queries::WARPS_DELETE, [
            "warp_name" => $warp->getName()
        ]);

        unset($this->warps[array_search($warp, $this->warps)]);
    }

    /**
     * Get a list of all warps registered on the server
     *
     * @return array
     */
    public function getWarps() : array
    {
        return $this->warps;
    }

}