-- #!mysql
-- #{paroxityecon

-- #  {init
CREATE TABLE IF NOT EXISTS economy (
  uuid      VARCHAR(36) PRIMARY KEY,
  username  VARCHAR(16),
  money     FLOAT DEFAULT 0
);
-- #  }

-- #  {register
-- #    :uuid       string
-- #    :username   string
-- #    :money      float
INSERT INTO economy (
  uuid, username, money
)
VALUES (
  :uuid, :username, :money
)
-- #  }

-- #  {add
-- #    {by-username
-- #        :username   string
-- #        :money      float
-- #        :max        float
UPDATE economy SET money = LEAST(money + :money, :max) WHERE username = LOWER(:username);
-- #    }

-- #    {by-uuid
-- #        :uuid   string
-- #        :money  float
-- #        :max    float
UPDATE economy SET money = LEAST(money + :money, :max) WHERE uuid = :uuid;
-- #    }
-- #  }

-- #  {deduct
-- #    {by-username
-- #        :username   string
-- #        :money      float
UPDATE economy SET money = GREATEST(money - :money, 0) WHERE username = LOWER(:username);
-- #    }

-- #    {by-uuid
-- #        :uuid   string
-- #        :money  float
UPDATE economy SET money = GREATEST(money - :money, 0) WHERE uuid = :uuid;
-- #    }
-- #  }

-- #  {set
-- #    {by-username
-- #        :username   string
-- #        :money      float
-- #        :max        float
UPDATE economy SET money = LEAST(:money, :max) WHERE username = LOWER(:username);
-- #    }

-- #    {by-uuid
-- #        :uuid   string
-- #        :money  float
-- #        :max    float
UPDATE economy SET money = LEAST(:money, :max) WHERE uuid = :uuid;
-- #    }
-- #  }

-- #  {get
-- #    {by-username
-- #      :username string
SELECT money FROM economy WHERE username = LOWER(:username);
-- #    }

-- #    {by-uuid
-- #      :uuid string
SELECT money FROM economy WHERE uuid = :uuid;
-- #    }

-- #    {top
SELECT * FROM economy ORDER BY money DESC;
-- #    }

-- #    {top10
SELECT * FROM economy ORDER BY money DESC LIMIT 10;
-- #    }
-- #  }

-- #}