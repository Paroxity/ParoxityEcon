-- #!sqlite
-- #{paroxityecon

-- #  {init
CREATE TABLE IF NOT EXISTS economy (
  player VARCHAR(30) COLLATE NOCASE NOT NULL,
  money INTEGER DEFAULT 0,
  PRIMARY KEY (player)
);
-- #  }

-- #  {update
-- #    :player string
-- #    :money int
INSERT OR REPLACE INTO economy (
  player, money
)
VALUES (
  :player, :money
)
-- #  }

-- #  {get
-- #    {player
-- #      :player string
SELECT money FROM economy WHERE player = :player;
-- #    }

-- #    {top10
SELECT * FROM economy ORDER BY money DESC LIMIT 10;
-- #    }

-- #    {top
SELECT * FROM economy ORDER BY money DESC;
-- #    }
-- #  }

-- #}