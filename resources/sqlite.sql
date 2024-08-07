-- #!sqlite
-- # { players
-- #  { initialize
CREATE TABLE IF NOT EXISTS players (
    uuid VARCHAR(36) PRIMARY KEY,
    username VARCHAR(16),
    balance INT DEFAULT 0,
    cooldowns TEXT
    );

CREATE TABLE IF NOT EXISTS cooldowns (
    uuid VARCHAR(36),
    entry VARCHAR,
    timestamp INT,
    PRIMARY KEY (uuid, entry),
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
    );
-- # }

-- #  { select
SELECT *
FROM players;
-- #  }

-- #  { create
-- #      :uuid string
-- #      :username string
-- #      :balance int
-- #      :cooldowns string
INSERT OR REPLACE INTO players(uuid, username, balance, cooldowns)
VALUES (:uuid, :username, :balance, :cooldowns);
-- #  }

-- #  { update
-- #      :uuid string
-- #      :username string
-- #      :balance int
-- #      :cooldowns string
UPDATE players
SET username=:username,
    balance=:balance,
    cooldowns=:cooldowns
WHERE uuid=:uuid;
-- #  }

-- #  { delete
-- #      :uuid string
DELETE FROM players
WHERE uuid=:uuid;
-- #  }

-- # { warps
-- #  { initialize
CREATE TABLE IF NOT EXISTS warps (
    warp_name VARCHAR(32) PRIMARY KEY NOT NULL,
    world_name VARCHAR(32) NOT NULL,
    x INT NOT NULL,
    y INT NOT NULL,
    z INT NOT NULL
    );
-- #  }
-- #  { select
SELECT *
FROM warps;
-- #  }
-- #  { create
-- #      :warp_name string
-- #      :world_name string
-- #      :x int
-- #      :y int
-- #      :z int
INSERT OR REPLACE INTO warps(warp_name, world_name, x, y, z)
VALUES (:warp_name, :world_name, :x, :y, :z);
-- #  }
-- #  { delete
-- #      :warp_name int
DELETE FROM warps
WHERE warp_name=:warp_name;
-- #  }
-- # }

-- # { homes
-- #  { initialize
CREATE TABLE IF NOT EXISTS homes (
                                     uuid VARCHAR(36),
    home_name VARCHAR(32),
    world_name VARCHAR(32),
    x INT,
    y INT,
    z INT,
    max_homes INT DEFAULT 3,
    PRIMARY KEY (uuid, home_name)
    );
-- #  }
-- # { select
SELECT *
FROM homes;
-- # }

-- #  { create
-- #      :uuid string
-- #      :home_name string
-- #      :world_name string
-- #      :x int
-- #      :y int
-- #      :z int
-- #      :max_homes int
INSERT OR REPLACE INTO homes(uuid, home_name, world_name, x, y, z, max_homes)
VALUES (:uuid, :home_name, :world_name, :x, :y, :z, :max_homes);
-- #  }

-- #  { delete
-- #      :uuid string
-- #      :home_name string
DELETE FROM homes
WHERE uuid = :uuid AND home_name = :home_name;
-- #  }

-- #  { update
-- #      :uuid string
-- #      :max_homes int
UPDATE homes
SET max_homes = :max_homes
WHERE uuid = :uuid;
-- #   }
-- #  }
-- # }