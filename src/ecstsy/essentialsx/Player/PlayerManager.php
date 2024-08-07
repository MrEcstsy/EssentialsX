<?php

declare(strict_types=1);

namespace ecstsy\essentialsx\Player;

use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Queries;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PlayerManager
{
    use SingletonTrait;

    /** @var EssentialPlayer[] */
    private array $sessions; // array to fetch player data

    public function __construct(
        public Loader $plugin
    ){
        self::setInstance($this);

        $this->loadSessions();
    }

    /**
     * Store all player data in $sessions property
     *
     * @return void
     */
    private function loadSessions(): void
    {
        Loader::getDatabase()->executeSelect(Queries::PLAYERS_SELECT, [], function (array $rows): void {
            foreach ($rows as $row) {
                $this->sessions[$row["uuid"]] = new EssentialPlayer(
                    Uuid::fromString($row["uuid"]),
                    $row["username"],
                    $row["balance"],
                    $row["cooldowns"],
                );
            }
        });
    }

    /**
     * Create a session
     *
     * @param Player $player
     * @return EssentialPlayer
     * @throws \JsonException
     */
    public function createSession(Player $player): EssentialPlayer
    {
        $args = [
            "uuid" => $player->getUniqueId()->toString(),
            "username" => $player->getName(),
            "balance" => Loader::getInstance()->getConfig()->get("starting-balance"),
            "cooldowns" => "{}",
        ];

        Loader::getDatabase()->executeInsert(Queries::PLAYERS_CREATE, $args);

        $this->sessions[$player->getUniqueId()->toString()] = new EssentialPlayer(
            $player->getUniqueId(),
            $args["username"],
            $args["balance"],
            $args["cooldowns"],
        );
        return $this->sessions[$player->getUniqueId()->toString()];
    }

    /**
     * Get session by player object
     *
     * @param Player $player
     * @return EssentialPlayer|null
     */
    public function getSession(Player $player) : ?EssentialPlayer
    {
        return $this->getSessionByUuid($player->getUniqueId());
    }

    /**
     * Get session by player name
     *
     * @param string $name
     * @return EssentialPlayer|null
     */
    public function getSessionByName(string $name) : ?EssentialPlayer
    {
        foreach ($this->sessions as $session) {
            if (strtolower($session->getUsername()) === strtolower($name)) {
                return $session;
            }
        }
        return null;
    }

    /**
     * Get session by UuidInterface
     *
     * @param UuidInterface $uuid
     * @return EssentialPlayer|null
     */
    public function getSessionByUuid(UuidInterface $uuid) : ?EssentialPlayer
    {
        return $this->sessions[$uuid->toString()] ?? null;
    }

    public function destroySession(EssentialPlayer $session) : void
    {
        Loader::getDatabase()->executeChange(Queries::PLAYERS_DELETE, ["uuid", $session->getUuid()->toString()]);

        # Remove session from the array
        unset($this->sessions[$session->getUuid()->toString()]);
    }

    public function getSessions() : array
    {
        return $this->sessions;
    }

}