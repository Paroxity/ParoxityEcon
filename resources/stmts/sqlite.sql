-- #!sqlite
-- #{paroxityecon

-- #  {init
CREATE TABLE IF NOT EXISTS economy (
  uuid      VARCHAR(36) PRIMARY KEY,
  money     FLOAT DEFAULT 0,
  FOREIGN KEY (uuid) REFERENCES records(uuid)
);
-- #  }

-- #  {register
-- #    :uuid       string
-- #    :money      float
INSERT INTO economy (
  uuid, money
)
VALUES (
  :uuid, :money
)
-- #  }

-- #  {add
-- #    {by-username
-- #        :username   string
-- #        :money      float
-- #        :max        float
UPDATE economy SET money = MIN(money + :money, :max) WHERE uuid = (SELECT uuid FROM records WHERE username = LOWER(:username));
-- #    }

-- #    {by-uuid
-- #        :uuid   string
-- #        :money  float
-- #        :max    float
UPDATE economy SET money = MIN(money + :money, :max) WHERE uuid = :uuid;
-- #    }
-- #  }

-- #  {deduct
-- #    {by-username
-- #        :username   string
-- #        :money      float
UPDATE economy SET money = MAX(money - :money, 0) WHERE uuid = (SELECT uuid FROM records WHERE username = LOWER(:username));
-- #    }

-- #    {by-uuid
-- #        :uuid   string
-- #        :money  float
UPDATE economy SET money = MAX(money - :money, 0) WHERE uuid = :uuid;
-- #    }
-- #  }

-- #  {set
-- #    {by-username
-- #        :username   string
-- #        :money      float
-- #        :max        float
UPDATE economy SET money = MIN(:money, :max) WHERE uuid = (SELECT uuid FROM records WHERE username = LOWER(:username));
-- #    }

-- #    {by-uuid
-- #        :uuid   string
-- #        :money  float
-- #        :max    float
UPDATE economy SET money = MIN(:money, :max) WHERE uuid = :uuid;
-- #    }
-- #  }

-- #  {get
-- #    {by-username
-- #      :username string
SELECT money FROM economy WHERE uuid = (SELECT uuid FROM records WHERE username = LOWER(:username));
-- #    }

-- #    {by-uuid
-- #      :uuid string
SELECT money FROM economy WHERE uuid = :uuid;
-- #    }

-- #    {top
SELECT economy.money, records.username, records.display_name
FROM economy, records
WHERE economy.uuid = records.uuid
ORDER BY economy.money DESC;
-- #    }

-- #    {top10
SELECT economy.money, records.username, records.display_name
FROM economy, records
WHERE economy.uuid = records.uuid
ORDER BY economy.money DESC LIMIT 10;
-- #    }
-- #  }

-- #}