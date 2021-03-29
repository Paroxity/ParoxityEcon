-- #!mysql
-- #{paroxityecon

-- #  {economy
-- #    {init
CREATE TABLE IF NOT EXISTS economy (
  uuid          VARCHAR(36) PRIMARY KEY,
  currency_id   VARCHAR(36) NOT NULL,
  money         FLOAT DEFAULT 0,
  FOREIGN KEY (uuid) REFERENCES records(uuid) ON DELETE CASCADE,
	FOREIGN KEY (currency_id) REFERENCES currency(id) ON DELETE CASCADE
	);
-- #    }

-- #    {register
-- #      :uuid         string
-- #      :currency_id  string
-- #      :money        float
INSERT INTO economy (
  uuid, currency_id, money
)
VALUES (
  :uuid, :currency_id, :money
)
-- #    }

-- #    {add
-- #      {by-username
-- #          :username     string
-- #          :currency_id  string
-- #          :money        float
-- #          :max          float
UPDATE economy SET money = LEAST(money + :money, :max)
WHERE uuid = (SELECT uuid FROM records WHERE username = LOWER(:username)) AND currency_id = :currency_id;
-- #    }

-- #      {by-uuid
-- #          :uuid         string
-- #          :currency_id  string
-- #          :money        float
-- #          :max          float
UPDATE economy SET money = LEAST(money + :money, :max) WHERE uuid = :uuid AND currency_id = :currency_id;
-- #      }
-- #    }

-- #    {deduct
-- #      {by-username
-- #          :username     string
-- #          :currency_id  string
-- #          :money        float
UPDATE economy SET money = GREATEST(money - :money, 0)
WHERE uuid = (SELECT uuid FROM records WHERE username = LOWER(:username)) AND currency_id = :currency_id;
-- #      }

-- #      {by-uuid
-- #          :uuid         string
-- #          :currency_id  string
-- #          :money        float
UPDATE economy SET money = GREATEST(money - :money, 0) WHERE uuid = :uuid AND currency_id = :currency_id;
-- #      }
-- #    }

-- #    {set
-- #      {by-username
-- #          :username     string
-- #          :currency_id  string
-- #          :money        float
-- #          :max          float
UPDATE economy SET money = LEAST(:money, :max) WHERE uuid = (SELECT uuid FROM records
WHERE username = LOWER(:username)) AND currency_id = :currency_id;
-- #      }

-- #      {by-uuid
-- #          :uuid         string
-- #          :currency_id  string
-- #          :money        float
-- #          :max          float
UPDATE economy SET money = LEAST(:money, :max) WHERE uuid = :uuid AND currency_id = :currency_id;
-- #      }
-- #    }

-- #    {get
-- #      {by-username
-- #        :username     string
-- #        :currency_id  string
SELECT money FROM economy WHERE uuid = (SELECT uuid FROM records
WHERE username = LOWER(:username)) AND currency_id = :currency_id;
-- #      }

-- #      {by-uuid
-- #        :uuid         string
-- #        :currency_id  string
SELECT money FROM economy WHERE uuid = :uuid AND currency_id = :currency_id;
-- #      }

-- #      {top
-- #        :currency_id  string
SELECT economy.money, records.username, records.display_name
FROM economy, records
WHERE economy.uuid = records.uuid AND currency_id = :currency_id
ORDER BY economy.money DESC;
-- #      }

-- #      {top10
-- #        :currency_id  string
SELECT economy.money, records.username, records.display_name
FROM economy, records
WHERE economy.uuid = records.uuid AND currency_id = :currency_id
ORDER BY economy.money DESC LIMIT 10;
-- #      }
-- #    }
-- #  }

-- #  {currency
-- #    {init
CREATE TABLE IF NOT EXISTS currency (
  id                VARCHAR(36) NOT NULL UNIQUE PRIMARY KEY,
  name              VARCHAR(36) NOT NULL,
	is_default        BOOLEAN DEFAULT false,
  symbol            VARCHAR(36) NOT NULL,
  symbol_position   VARCHAR(36) NOT NULL,
  starting_amount   FLOAT DEFAULT 0,
  maximum_amount   FLOAT DEFAULT 0
);
-- #    }

-- #    {add
-- #      :id               string
-- #      :name             string
-- #      :is_default       bool
-- #      :symbol           string
-- #      :symbol_position  string
-- #      :starting_amount  float
-- #      :maximum_amount   float
INSERT INTO currency (
	id, name, is_default, symbol, symbol_position, starting_amount, maximum_amount
)
VALUES (
  :id, :name, :is_default, :symbol, :symbol_position, :starting_amount, :maximum_amount
);
-- #    }

-- #    {update
-- #      :id               string
-- #      :name             string
-- #      :is_default       bool
-- #      :symbol           string
-- #      :symbol_position  string
-- #      :starting_amount  float
-- #      :maximum_amount   float
UPDATE currency SET
  name            = :name,
  is_default      = :is_default,
  symbol          = :symbol,
  symbol_position = :symbol_position,
  starting_amount = :starting_amount,
  maximum_amount  = :maximum_amount
WHERE id = :id;
-- #    }

-- #    {get
-- #      {all
SELECT * FROM currency;
-- #      }
-- #      {via-id
-- #        :id string
SELECT * FROM currency WHERE id = :id;
-- #      }
-- #    }

-- #    {delete
-- #      :id string
DELETE FROM currency WHERE id = :id;
-- #    }
-- #  }
-- #}